<?php
namespace App\Http\Controllers\Portal;
use App\Enums\Assignment\SubmissionType;
use App\Exceptions\Dams\MediaNotFoundException;
use App\Http\Controllers\PortalBaseController;
use App\Model\ChannelAnalytic\Repository\IOverAllChannalAnalyticRepository;
use App\Services\Assignment\IAssignmentAttemptService;
use App\Services\Assignment\IAssignmentService;
use App\Services\DAMS\IDAMsService;
use App\Services\Post\IPostService;
use App\Services\Program\IProgramService;
use Illuminate\Support\Facades\Validator;
use Auth;
use Carbon;
use Config;
use Exception;
use Input;
use Log;
use Redirect;
use Request;
use ZipArchive;

class AssignmentController extends PortalBaseController
{

    /**
     * @var \App\Services\Assignment\IAssignmentAttemptService
     */
    private $assignment_attempt_service;

    /**
     * @var \App\Services\Assignment\IAssignmentService
     */
    private $assignment_service;

    /**
     * @var \App\Services\DAMS\IDAMsService
     */
    private $dams_service;

    /**
     * @var \App\Services\Program\IProgramService
     */
    private $program_service;

    /**
     * @var \App\Model\ChannelAnalytic\Repository\IOverAllChannalAnalyticRepository
     */
    private $overall_channel_analytic_repo;

    /**
     * @var \App\Services\Post\IPostService
     */
    private $post_service;

	public function __construct(
        Request $request,
        IAssignmentAttemptService $assignment_attempt_service,
        IAssignmentService $assignment_service,
        IDAMsService $dams_service,
        IProgramService $program_service,
        IOverAllChannalAnalyticRepository $overall_channel_analytic_repo,
        IPostService $post_service
    )
    {
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);
        $this->assignment_attempt_service = $assignment_attempt_service;
        $this->assignment_service = $assignment_service;
        $this->dams_service = $dams_service;
        $this->program_service = $program_service;
        $this->overall_channel_analytic_repo = $overall_channel_analytic_repo;
        $this->post_service = $post_service;
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
    }
    public function getIndex()
    {
        $this->getListAssignments();
    }

    public function getListAssignments() {
        $filter = Input::get('filter', 'unattempted');
        $start = Input::get('start', 0);
        $limit = 9;
        try {
            $user_assignment_rel = collect($this->assignment_service->getAllAssignmentsAssigned());
        } catch (\Exception $e) {
            Log::info($e);
            $user_assignment_rel = collect(['seq_assignments' => [], 'assignment_list' => [], 'feed_assignment_list' => []]);
        }
        $seqAssignments = $user_assignment_rel->get('seq_assignments', []);

        $assignment_list = array_unique($user_assignment_rel->get('assignment_list', []));

        // code for completed assignments
        $attempted_data = $this->assignment_attempt_service->getAssignmentAttemptByUserAndAssignmentIds(
                (int)Auth::user()->uid,
                $assignment_list
        );
        $attempt = [];
        $draft = [];
        foreach ($attempted_data as $value) {
            if($value->submission_status == SubmissionType::SAVE_AS_DRAFT){
                $draft[] = $value->assignment_id;
            } else {
                $attempt[] = $value->assignment_id;
            }
        }
        $count['attempted'] = count($attempt);
        $attempted = $this->assignment_service->getAssignmentByIds($attempt);


        // code for unattempted assignments
        $myUAAssignmentIds = [];
        $myUAAssignmentIds = array_diff($assignment_list, $attempt);
        $nonSquAssignments = array_diff($myUAAssignmentIds, $seqAssignments);
        $unattempted = $this->assignment_service->getAssignmentByIds($nonSquAssignments);
        $count['unattempted'] = $unattempted->count();

        // code for assignment reports
        $total_attended = [];
        $total_attended = array_merge($draft, $attempt);
        $count['reports'] = count($total_attended);
        $attended_assignments = $this->assignment_service->getAssignmentByIds($total_attended);

        switch ($filter) {
            case 'unattempted':
                $assignments = $unattempted->slice($start, $limit);
                break;
            case 'attempted':
                $assignments = $attempted->slice($start, $limit);
                break;
            case 'reports':
                $assignments = $attended_assignments;
                break;
            default:
                return parent::getError($this->theme, $this->theme_path);
                break;
        }

        if (Request::ajax()) {
            if($filter != "reports"){
                if(!$assignments->isEmpty()) {
                    return response()->json([
                        'status' => true,
                        'data' => view(
                                $this->theme_path . '.assignment.assignment_ajax_load',
                                [
                                    'assignments' => $assignments,
                                    'completed_list' => $attempted,
                                    'drafted_list' => $draft,
                                    'filter' => $filter,
                                    'count' => $count
                                ])->render(),
                    ]);
                }
                else {
                    return response()->json([
                        'status' => false,
                        'data' => 'No more assignments to show',
                    ]);
                }
            }
            else {
                if(!$assignments->isEmpty()) {
                    return response()->json([
                        'status' => true,
                        'data' => view(
                                $this->theme_path . '.assignment.assignment_report',
                                [
                                    'assignments' => $assignments,
                                    'attempted_data' => $attempted_data,
                                    'drafted_list' => $draft,
                                    'filter' => $filter,
                                    'count' => $count
                                ])->render(),
                    ]);
                }
            }
        } else {
            $this->layout->pagetitle = "Assignments";
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $this->layout->content = view($this->theme_path . '.assignment.list_assignments')
                ->with('assignments', $assignments)
                ->with('filter', $filter)
                ->with('count', $count)
                ->with('completed_list', $attempted)
                ->with('attempted_data', $attempted_data)
                ->with('drafted_list', $draft);
        }

    }

    /**
     * @param string $packet_slug
     * @param int $assignment_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getAssignmentResult($packet_slug, $assignment_id)
    {
        $user_id = (int)Auth::user()->uid;
        $assignment_id = (int)$assignment_id;
        $assignment_details = $this->assignment_service->getAssignments(["id" => [$assignment_id]])->first();
        $attempt_assignment_details = $this->assignment_attempt_service->getAllAttempts(["assignment_id" => $assignment_id, "user_id" => $user_id])->first();
        $this->layout->pagetitle = "Assignments";
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.assignment.assignment_result')
                                ->with('packet_slug', $packet_slug)
                                ->with('assignment', $assignment_details)
                                ->with('attempt_assignment', $attempt_assignment_details);
    }

    /**
     * @param string $packet_slug
     * @param int $assignment_id
     * @param string $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getAssignmentDetails($packet_slug = "unattempted", $assignment_id)
    {
        $assignment_id = (int)$assignment_id;
        $assignment = $this->assignment_service->getAssignments(["id" => [$assignment_id]])->first();
        $assignment_attempt = $this->assignment_attempt_service->getAllAttempts(["assignment_id" => $assignment_id, "user_id" => Auth::user()->uid])->first();

        $current_time = Carbon::now(Auth::user()->timezone);
        $assignment_start_date = $assignment->start_time;
        $assignment_cut_off_date = $assignment->cutoff_time;
        if(!(($current_time >= $assignment_start_date) && ($current_time <= $assignment_cut_off_date))) {
            return parent::getError($this->theme, $this->theme_path, 401);
        }

        $this->layout->pagetitle = "Assignments";
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.assignment.assignment_details')
                                ->with('packet_slug', $packet_slug)
                                ->with('assignment', $assignment)
                                ->with('assignment_attempt', $assignment_attempt);
                                
    }

    /**
     * @param string $packet_slug
     * @param int $assignment_id
     * @param string $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSubmitAssignment($packet_slug, $assignment_id)
    {
        try {
            $inputs = Request::all();
            $form_action = array_get($inputs, 'form_action');
            $assignment_id = (int)$assignment_id;
            $user_id = (int)Auth::user()->uid;
            $assignment = $this->assignment_service->getAssignments(["id" => [$assignment_id]])->first();
            $assignment_id = (int)$assignment->id;
            $submission_type = $assignment->submission_type;
            $assignment_attempt = $this->assignment_attempt_service->getAllAttempts(["assignment_id" => $assignment_id, "user_id" => $user_id])->first();
            if (!empty($assignment_attempt->submission_status)) {
                if (($assignment_attempt->submission_status == SubmissionType::YET_TO_REVIEW) || ($assignment_attempt->submission_status == SubmissionType::LATE_SUBMISSION)) {
                    return redirect('assignment/assignment-details/'.$packet_slug.'/'.$assignment_id)->with('error', trans('assignment.submitted_assignment'));
                }
            }
                   
            $current_time = Carbon::now(Auth::user()->timezone);
            $rules = $messages = [] ;
            if ($submission_type == "file_submission") {
                $max_no_file_allowed = $assignment->max_no_file_allowed;
                $rules = ['multiple_files' => 'required|checkfileexists|fileformat|checkmimetype|max_num_files|max:' . config('app.assignments_max_upload_size')*1024, 'uploaded_text' => 'Required'];
                Validator::extend('checkfileexists', function ($attribute, $value, $parameters) {
                    $value = array_filter($value);
                    if (!empty($value)) {
                        return true;
                    }
                    return false;
                });
                Validator::extend('checkmimetype', function ($attribute, $value, $parameters) {
                    $value = array_filter($value);
                    if (!empty($value)) {
                        foreach ($value as $key => $file) {
                            if (in_array($file->getClientMimeType(),config('app.assignments_document_mime_types'))) {
                                return true;
                            }
                            return false;
                        }
                    }
                });
                Validator::extend('fileformat', function ($attribute, $value, $parameters) {
                    $value = array_filter($value);
                    if (!empty($value)) {
                        foreach ($value as $key => $file) {
                            $extension = $file->getClientOriginalExtension();
                            if (in_array($extension, config('app.assignments_document_extensions'))) {
                                return true;
                            }
                            return false;
                        }
                    }
                });
                Validator::extend('max_num_files', function ($attribute, $value, $parameters) use ($max_no_file_allowed) {
                    $value = array_filter($value);
                    if (!empty($value)) {
                        if (count($value) <= $max_no_file_allowed) {
                            return true;
                        }
                        return false;
                    }
                });
                $messages = [
                    'multiple_files.max' => trans('assignment.max_assignment'),
                    'checkfileexists' => trans('assignment.file_require'),
                    'checkmimetype' => trans('admin/assignment.check_doc_extension'),
                    'fileformat' => trans('admin/assignment.mimetype_error'),
                    'max_num_files' => str_replace(':attribute', $max_no_file_allowed, trans('assignment.max_num_files_allow')),
                    'uploaded_text.required' => trans('assignment.edit_assignment_require'),
                ];
            } else {
               $rules = ['uploaded_text' => 'Required'];
               $messages = ['uploaded_text.required' => trans('assignment.edit_assignment_require')]; 
            }
            $validation = Validator::make(Request::all(), $rules, $messages);
            if ($validation->fails()) {
                return Redirect::route('submit-assignment', ['packet_slug' => $packet_slug, 'assignment_id' => $assignment_id])->withInput()->withErrors($validation);
            } else {
                $uploaded_file = [];
                $data = [];
                if ($submission_type == "file_submission") {
                    $multiple_files = array_get($inputs, 'multiple_files', []);
                    foreach ($multiple_files as $key => $file) {
                       $random_filename = strtolower(str_random(32));
                        $public_assignments_documents_path = Config::get('app.public_assignments_documents_path');

                        $uploaded_file[$key] = [
                            'unique_name' => $random_filename,
                            'unique_name_with_extension' => $random_filename . '.' . $file->getClientOriginalExtension(),
                            'file_client_name' => $file->getClientOriginalName(),
                            'file_size' => $file->getSize(),
                            'file_extension' => $file->getClientOriginalExtension(),
                            'mimetype' => $file->getMimeType()
                        ];
                        $uploaded_file[$key]['public_file_location'] = $public_assignments_documents_path . $random_filename . '.' . $file->getClientOriginalExtension();
                        $file->move($public_assignments_documents_path, $uploaded_file[$key]['public_file_location']);
                    }
                }
                if ($form_action == "submit") {
                    if (($current_time >= $assignment->end_time) && ($current_time <= $assignment->cutoff_time)) {
                        $data['submission_status'] = SubmissionType::LATE_SUBMISSION;
                    } else {
                        $data['submission_status'] = SubmissionType::YET_TO_REVIEW;
                    }
                } else {
                    $data['submission_status'] = SubmissionType::SAVE_AS_DRAFT;
                }
                if (!empty($assignment_attempt)) {
                    $saved_files = array_get($assignment_attempt, 'uploaded_file');
                    $file_path = array_pluck($saved_files, 'public_file_location');
                    $details = [
                        'assignment_attempt' => $assignment_attempt,
                        'uploaded_file' => $uploaded_file,
                        'inputs' => $inputs,
                        'data' => $data,
                        'user_id' => $user_id
                    ];
                    $update_data = $this->updateAssignmentAttempt($details);
                    if ($update_data) {
                        if (!empty($file_path)) {
                            foreach ($file_path as $value) {
                                unlink(public_path($value));
                            }
                        }
                        if ($form_action == "submit") {
                            if (!empty($assignment->post_id)) {
                                $this->putEntryInToOca((int)$assignment_id);
                            }
                            return Redirect::route('assignment-result', ['packet_slug' => $packet_slug, 'assignment_id' => $assignment_id])->with('success', trans('assignment.assignment_success'));
                        } else {
                            return Redirect::route('submit-assignment', ['packet_slug' => $packet_slug, 'assignment_id' => $assignment_id])->with('success', trans('assignment.draft_message'));
                        }
                    }
                } else {
                    $data += [
                        'id' => $this->assignment_attempt_service->getNextSequence(),
                        'assignment_id' => $assignment_id,
                        'user_id' => $user_id,
                        'uploaded_file' => $uploaded_file,
                        'uploaded_text' => html_entity_decode(array_get($inputs, 'uploaded_text')),
                        'submitted_at' => [time()],
                        'grade' => 0,
                        'created_at' => time()
                    ];
                    $insert_data = $this->assignment_attempt_service->insertData($data);
                    if ($insert_data) {
                        if ($form_action == "submit") {
                            if (!empty($assignment->post_id)) {
                                $this->putEntryInToOca((int)$assignment_id);
                            }
                            return Redirect::route('assignment-result', ['packet_slug' => $packet_slug, 'assignment_id' => $assignment_id])->with('success', trans('assignment.assignment_success'));
                        } else {
                            return Redirect::route('submit-assignment', ['packet_slug' => $packet_slug, 'assignment_id' => $assignment_id])->with('success', trans('assignment.draft_message'));
                        }
                    }
                }
            }
        } catch(Exception $e) {
            Log::error($e->getMessage() . ' at line '. $e->getLine(). ' in file '. $e->getFile());
            return redirect('assignment/assignment-details/'.$packet_slug.'/'.$assignment_id)->with('error', trans('assignment.error_message'));
        }


    }

    /**
     * @param int $assignment_id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getDownloadTemplateFile($assignment_id, $packet_slug)
    {
        try {
            $assignment_id = (int)$assignment_id;
            $assignment = $this->assignment_service->getAssignments(["id" => [$assignment_id]])->first();
            $template_file_id = $assignment->template_file_id;
            $dams = $this->dams_service->getMedia($template_file_id, '_id');
            if (!file_exists($dams->public_file_location)) {
                throw new MediaNotFoundException;
            }
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.$dams->file_client_name);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($dams->public_file_location));
            ob_clean();
            flush();
            readfile($dams->public_file_location);
        } catch (MediaNotFoundException $mnf) {
            Log::error("Media not fount exception while download $assignment_id". $mnf->getMessage());
            return redirect('assignment/assignment-details/'.$packet_slug.'/'.$assignment_id)->with('error', trans('assignment.document_message'));
        } catch (Exception $e) {
            Log::error($e->getMessage() . ' at line '. $e->getLine(). ' in file '. $e->getFile());
            return redirect('assignment/assignment-details/'.$packet_slug.'/'.$assignment_id)->with('error', trans('assignment.error_message'));
        }
        exit;
    }

    /**
     * @param int $assignment_id
     * @param  int user_id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws Exception
     */
    public function getDownloadUploadedFiles($assignment_id)
    {
        try {
            $assignment_id = (int)$assignment_id;
            $user_id = (int) Input::get('user_id', Auth::user()->uid);
            $assignment = $this->assignment_service->getAssignments(["id" => [$assignment_id]])->first();
            $assignment_attempt = $this->assignment_attempt_service->getAllAttempts(["assignment_id" => $assignment_id, "user_id" => $user_id])->first();
            $uploaded_file = $assignment_attempt->uploaded_file;
            $files = [];
            if (count($uploaded_file) == 1) {
                $uploaded_file = array_get($uploaded_file, 0);
                $mimetype = array_get($uploaded_file, 'mimetype');
                if (($mimetype == "application/zip") || ($mimetype == "application/x-rar-compressed") || ($mimetype == "application/x-tar")) {
                    return response()->download(public_path(array_get($uploaded_file, 'public_file_location')), array_get($uploaded_file, 'file_client_name'));
                } else {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.array_get($uploaded_file, 'file_client_name'));
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize(array_get($uploaded_file, 'public_file_location')));
                    ob_clean();
                    flush();
                    readfile(array_get($uploaded_file, 'public_file_location'));
                    exit;
                }
            } else {
                // create a list of files that should be added to the archive.
                foreach ($uploaded_file as $key => $value) {
                   $files[array_get($value, 'file_client_name')] = public_path(array_get($value, 'public_file_location'));
                }

                // define the name of the archive and create a new ZipArchive instance.
                $archiveFile = public_path("assignments/".$assignment->name.".zip");
                $archive = new ZipArchive();

                // check if the archive could be created.
                if ($archive->open($archiveFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                    // loop trough all the files and add them to the archive.
                    foreach ($files as $name => $file) {
                        if ($archive->addFile($file, $name)) {
                            // do something here if addFile succeeded, otherwise this statement is unnecessary and can be ignored.
                            continue;
                        } else {
                            throw new Exception("file `{$file}` could not be added to the zip file: " . $archive->getStatusString());
                        }
                    }

                    // close the archive.
                    if ($archive->close()) {
                        // archive is now downloadable ...
                        return response()->download($archiveFile, basename($archiveFile))->deleteFileAfterSend(true);
                    } else {
                        throw new Exception("could not close zip file: " . $archive->getStatusString());
                    }
                } else {
                  throw new Exception("zip file could not be created: " . $archive->getStatusString());
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage() . ' at line '. $e->getLine(). ' in file '. $e->getFile());
        }
    }

    /**
     * @param array $details
     * @return boolean
     */
    public function updateAssignmentAttempt($details)
    {
        $assignment_attempt = array_get($details, 'assignment_attempt');
        $uploaded_file = array_get($details, 'uploaded_file');
        $inputs = array_get($details, 'inputs');
        $data = array_get($details, 'data');
        $attempt_assignment_id = $assignment_attempt->id;
        $submitted_at = array_merge($assignment_attempt->submitted_at, [time()]);
        $user_id = array_get($details, 'user_id');
        $data += [
                'uploaded_file' => $uploaded_file,
                'uploaded_text' => html_entity_decode(array_get($inputs, 'uploaded_text')),
                'submitted_at' => $submitted_at,
                'updated_at' => time()
            ];
        $update_data = $this->assignment_attempt_service->updateData($attempt_assignment_id, $user_id, $data);
        return $update_data;
    }

    /**
     * @param int $assignment_id
     * @return boolean
     */
    public function putEntryInToOca($assignment_id)
    {   
        $user_id = (int)Auth::user()->uid;
        $assignment = $this->assignment_service->getAssignments(["id" => [$assignment_id]])->first();
        $post_id = $assignment->post_id;
        $packet = $this->post_service->getPacketByID((int)$post_id);
        $feed_slug = array_get($packet, 'feed_slug');
        $program = $this->program_service->getProgramBySlug('content_feed', $feed_slug);
        $channelId = $program->program_id;
        $isExists = $this->overall_channel_analytic_repo->isExists($channelId, $user_id);
        $postCountChannel = $this->post_service->getAllPacketsByFeedSlug($feed_slug, 'ACTIVE')->count();
        $returnFlag = true;
        $isViewedEle = false;
        $completion = 0;
        $postCompletion = [];
        $itemDetails = [];
        $postKey = 'p_' . $post_id;
        $viewedElement = 'assignment_' . $assignment_id;
        $countEle = 1;
        $postElement = [];
        if (isset($packet['elements']) && !empty($packet['elements'])) {
            foreach ($packet['elements'] as $element) {
                $postElement[] = $element['type'] . '_' . $element['id'];
            }
            $countEle = count($postElement);
        }
        $data = [];
        $data['updated_at'] = time();
        $data['user_id'] = $user_id;
        $data['channel_id'] = $channelId;
        $data['post_count'] = $postCountChannel;

        //Record exists in overall channel analytic 
        if (!is_null($isExists) || !empty($isExists)) {
            $existsPostCompletion = $isExists->post_completion;
            $existsItemDetails = $isExists->item_details;
            if (isset($existsItemDetails[$postKey])) {
                $tempPostEleRaw = $existsItemDetails[$postKey];
                $tempPostEle = array_unique($tempPostEleRaw);
                if (in_array($viewedElement, $tempPostEle)) {
                    $isViewedEle = true;
                }
                $tempPostEle[] = $viewedElement;
                $tempPostEle = array_unique($tempPostEle);
                $viewedCount = count(array_intersect($tempPostEle, $postElement));
                $existsPostCompletion[$postKey] = round(
                    ($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100,
                    2
                );
                $existsItemDetails[$postKey] = $tempPostEle;
            } else {
                $tempPostEle = [];
                $tempPostEle[] = $viewedElement;
                $viewedCount = count(array_intersect($tempPostEle, $postElement));

                $existsPostCompletion[$postKey] = $postCompletion[$postKey] = round(
                    (($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100),
                    2
                );
                $existsItemDetails[$postKey] = $itemDetails[$postKey] = $tempPostEle;
            }
            $completion = round(
                (array_sum(array_values($existsPostCompletion))) /
                (($postCountChannel > 1) ? $postCountChannel : 1),
                2
            );

            $data['item_details'] = $existsItemDetails;
            $data['post_completion'] = $existsPostCompletion;
            $data['completion'] = $completion;
            if ($data['completion'] >= 100) {
                if (isset($isExists->completed_at) && !empty($isExists->completed_at) && !$isViewedEle) {
                    $data['completed_at'] = $isExists->completed_at;
                    $data['completed_at'][] = time();
                } else {
                    $data['completed_at'] = [time()];
                }
            }
            $res = $this->overall_channel_analytic_repo->updateData(
                $data,
                $data['channel_id'],
                $data['user_id']
            );
            if (!$res) {
                $returnFlag = false;
            }
        } else {
            //No record in overall channel analytic
            $tempPostEle = [];
            $tempPostEle[] = $viewedElement;
            $viewedCount = count(array_intersect($tempPostEle, $postElement));
            //post_completion
            $postCompletion[$postKey] = round(
                ($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100,
                2
            );
            //item_details
            $itemDetails[$postKey] = $tempPostEle;
            //completion
            $completion = round(
                (array_sum(array_values($postCompletion))) /
                (($postCountChannel > 1) ? $postCountChannel : 1),
                2
            );

            
            $data['item_details'] = $itemDetails;
            $data['post_completion'] = $postCompletion;
            $data['completion'] = $completion;
            if ($data['completion'] >= 100) {
                $data['completed_at'] = [time()];
            }
            $data['created_at'] = time();
            $res = $this->overall_channel_analytic_repo->insertData($data);
            if (!$res) {
                $returnFlag = false;
            }
        }
        return $returnFlag;
    }

}


