<?php namespace App\Http\Controllers;

use Input;

class FileController extends Controller
{
    public function upload()
    {
        $max_size = 1048576; // 1MB
        $file = Input::file('upload');
        $funcNum = Input::get('CKEditorFuncNum');
        $id = Input::get('CKEditor');
        $url = $status = $name = $original_name = '';
        if (!empty($file) && $file->isValid()) {
            // get uploaded file extension
            $ext = $file->getClientOriginalExtension();
            // get size
            $size = $file->getClientSize();
            // looking for format and size validity
            $original_name = $file->getClientOriginalName();
            $name = time() . '_' . str_replace(' ', '-', trim($original_name));

            // Images
            if (in_array(strtolower($ext), ['jpeg', 'jpg', 'png'])) {
                $path = public_path() . '/images/';
                if (!is_dir($path)) {
                    $status = 'Destination directory does not exists';
                } elseif (!is_writable($path)) {
                    $status = 'Destination directory is not writable';
                } else {
                    if ($size < $max_size) {
                        // move uploaded file from temp to uploads directory
                        if ($file->move($path, $name)) {
                            $url = '/images/' . $name;
                        } else {
                            $status = 'Upload Fail: Unknown error occurred!';
                        }
                    } else {
                        $status = 'Upload Fail:It is too large to upload!. Allowed size is ' . round($max_size / 1024) . ' kb';
                    }
                }
            } else {
                $status = 'Upload Fail: Unsupported file format';
            }
        } else {
            $status = 'Upload Fail: Invalid input';
        }

        return "<script type='text/javascript'>
                    window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', function() {
                        var status = '$status';
                        if (status == '') {
                            var input = document.createElement('input');
                            input.setAttribute('type', 'hidden');
                            input.setAttribute('name', 'editor_images[]');
                            input.setAttribute('value', '$name');

                            //append to form element that you want .
                            parent.document.getElementById('$id').appendChild(input);

                            // Get the reference to a dialog window.
                            var element, dialog = this.getDialog();
                            // Check if this is the Image dialog window.
                            if ( dialog.getName() == 'image' ) {
                                element = dialog.getContentElement( 'info', 'txtAlt' );
                                if ( element ) element.setValue( '$original_name' );
                            }
                        } else {
                            alert(status);
                        }
                    });
                </script>";
    }

    public function browse()
    {
        $url = [];
        foreach (\File::allFiles(public_path() . '/images/') as $file) {
            $filename = $file->getRelativePathName();
            $url[]['url'] = '/images/' . $filename . '<br/>';
        }
        echo json_encode([$url]);
    }
}
