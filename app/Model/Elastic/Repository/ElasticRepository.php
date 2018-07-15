<?php
namespace App\Model\Elastic\Repository;

use App\Enums\Elastic\Types as ET;
use App\Libraries\Elastic\Elastic;
use Log;
use Exception;
/**
 * class ElasticRepository
 *
 * @package App\Model\Elastic\repository
 */
class ElasticRepository implements IElasticRepository
{
    /**
     * @var \App\Libraries\Elastic\Elastic
     */
    protected $elastic;

    /**
     * @var elastic index name
     */
    protected $elastic_index;

    /**
     * ElasticRepository constructor
     * @param Elastic $elastic
     */
    public function __construct(Elastic $elastic)
    {
        $this->elastic = $elastic;
        $this->elastic_index = config('elastic.params.index');
    }

    /**
     * {@inheritdoc}
     */
    public function addProgram($data)
    {
        $this->elastic->index([
            'index' => $this->elastic_index,
            'type' => ET::PROGRAM,
            'id' => $data['_id'],
            'body' => $data['body']
        ]);
        Log::info("Program(" . $data['body']['id'] . ") " . $data['body']['title'] . " indexed");
    }

    /**
     * {@inheritdoc}
     */
    public function updateProgram($data)
    {
        $this->elastic->update([
            'index' => $this->elastic_index,
            'type' => ET::PROGRAM,
            'id' => $data['_id'],
            'body' => [
                'doc' => $data['body']
            ],
        ]);
        Log::info("Program(" . $data['body']['id'] . ") " . $data['body']['title'] . " updated");
    }

    /**
     * {@inheritdoc}
     */
    public function removeProgram($program)
    {
        $this->elastic->delete([
            'index' => $this->elastic_index,
            'type' => ET::PROGRAM,
            'id' => $program->_id
        ]);
        Log::info("Program($program->proram_title) is removed");
        $this->deleteByQuery('program_id', $program->program_id, [ET::ITEM, ET::POST]);
    }

    /**
     * {@inheritdoc}
     */
    public function addPackage($data)
    {
        $this->elastic->index([
            'index' => $this->elastic_index,
            'type' => ET::PACKAGE,
            'id' => $data['_id'],
            'body' => $data['body']
        ]);
        Log::info("Package (" . $data['body']['id'] . ") " . $data['body']['title'] . " indexed");
    }

    /**
     * {@inheritdoc}
     */
    public function updatePackage($data)
    {
        $this->elastic->update([
            'index' => $this->elastic_index,
            'type' => ET::PACKAGE,
            'id' => $data['_id'],
            'body' => [
                'doc' => $data['body']
            ],
        ]);
        Log::info("Package (" . $data['body']['id'] . ") " . $data['body']['title'] . " updated");
    }

    /**
     * {@inheritdoc}
     */
    public function removePackage($package_id)
    {
        $this->elastic->delete([
            'index' => $this->elastic_index,
            'type' => ET::PACKAGE,
            'id' => $package_id
        ]);
        Log::info("Package($package_id) is removed");
    }

    /**
     * {@inheritdoc}
     */
    public function updateProgramSlug($program_id, $program_slug)
    {
        $this->elastic->updateByQuery([
            'index' => $this->elastic_index,
            'type' => [ET::ITEM, ET::POST],
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'term' => [
                                'program_id' => $program_id
                            ]
                        ]
                    ]
                ],
                "script" => [
                    'inline' => 'ctx._source.program_slug = params.program_slug',
                    'lang' => 'painless',
                    'params' => [
                        'program_slug' => $program_slug
                    ]
                ]
            ]
        ]);
        Log::info("Program($program_id) slug updated for items and posts");
    }

    /**
     * {@inheritdoc}
     */
    public function addPost($data)
    {
        $this->elastic->index([
                    'index' => $this->elastic_index,
                    'type' => ET::POST,
                    'id' => $data['_id'],
                    'body' => $data['body']
                ]);
        Log::info("Post(" . $data['body']['id'] . ") indexed");
    }

    /**
     * {@inheritdoc}
     */
    public function updatePost($data)
    {
        $this->elastic->update([
            'index' => $this->elastic_index,
            'type' => ET::POST,
            'id' => $data['_id'],
            'body' => [
                'doc' => $data['body']
            ],
        ]);
        Log::info("Post(" . $data['body']['id'] . ") indexed");
    }

    /**
     * {@inheritdoc}
     */
    public function removePost($post)
    {
        $this->elastic->delete([
            'index' => $this->elastic_index,
            'type' => ET::POST,
            'id' => $post['_id']
        ]);
        $this->deleteByQuery('post_id', $post['packet_id'], [ET::ITEM]);
    }

    /**
     * {@inheritdoc}
     */
    public function updatePostSlug($post_id, $post_slug, $sequential_access)
    {
        $this->elastic->updateByQuery([
            'index' => $this->elastic_index,
            'type' => ET::ITEM,
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'term' => [
                                'post_id' => $post_id
                            ]
                        ]
                    ]
                ],
                "script" => [
                    'inline' => 'ctx._source.slug = params.slug;ctx._source.sequential = params.sequential',
                    'lang' => 'painless',
                    'params' => [
                        'slug' => $post_slug,
                        'sequential' => $sequential_access
                    ]
                ]
            ]
        ]);
        Log::info("Post slug($post_slug) updated for items");
    }

    /**
     * {@inheritdoc}
     */
    public function addItems($post, $elements, $program)
    {
        $parameters = [];
        $sequential = array_get($post, 'sequential_access', 'no') == 'yes';
        $this->deleteByQuery('post_id', $post['packet_id'], [ET::ITEM]);
        if (!empty($elements)) {
            foreach ($elements as $element) {
                $parameters['body'][] = [
                    "index" => [
                        '_index' => $this->elastic_index,
                        '_type' => ET::ITEM,
                    ]
                ];

                $parameters['body'][] = [
                    'id' => $element['id'],
                    'program_id' => $program->program_id,
                    'post_id' => $post['packet_id'],
                    'title' => $element['name'],
                    'description' => array_get($element, 'display_name', ''),
                    'type' => $element['type'],
                    'sequential' => $sequential,
                    'slug' => $post['packet_slug'],
                    'program_slug' => $post['feed_slug'],
                    'user_ids' => $program->users,
                ];
            }
            $this->elastic->bulk($parameters);
            Log::info(count($elements) . "Items inserted for post " . $post['packet_title']);
        }
        $this->elastic->update([ //updating elements count in post
            'index' => $this->elastic_index,
            'type' => ET::POST,
            'id' => $post['_id'],
            'body' => [
                'doc' => [
                    'no_of_elements' => count($post['elements'])
                ]
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByQuery($field, $value, $types)
    {
        $this->elastic->deleteByQuery([
            'index' => $this->elastic_index,
            'type' => $types,
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'term' => [
                                $field => "$value"
                            ]
                        ]
                    ]
                ]
            ],
            'conflicts' => 'proceed',
        ]);
        Log::info("bulk delete query for field = " . $field . " ,value=" . $value . " types=" . implode(',', $types));
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateProgramUsers($program)
    {
        $this->elastic->update([
            'index' => $this->elastic_index,
            'type' => ET::PROGRAM,
            'id' => $program->_id,
            'body' => [
                'doc' => ['user_ids' => $program->users]
            ]
        ]);
        $this->elastic->updateByQuery([
            'index' => $this->elastic_index,
            'type' => [ET::ITEM, ET::POST],
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'term' => [
                                'program_id' => $program->program_id
                            ]
                        ]
                    ]
                ],
                "script" => [
                    'inline' => 'ctx._source.user_ids = params.users',
                    'lang' => 'painless',
                    'params' => [
                        'users' => $program->users,
                    ]
                ]
            ]
        ]);
    }

    public function updatePackageUsers($package)
    {
        $this->elastic->update([
            'index' => $this->elastic_index,
            'type' => ET::PACKAGE,
            'id' => $package->_id,
            'body' => [
                'doc' => ['user_ids' => $package->users]
            ]
        ]);
        Log::info("Package($package->package_id) $package->package_title users updated");
    }

    /**
     * {@inheritdoc}
     */
    public function addQuiz($data)
    {
        $this->elastic->index([
            'index' => $this->elastic_index,
            'type' => ET::ASSESSMENT,
            'id' => $data['_id'],
            'body' => $data['body']
        ]);
        Log::info("Quiz(" . $data['body']['id'] . ") " . $data['body']['title'] . " indexed");
    }

    /**
     * {@inheritdoc}
     */
    public function updateQuiz($data)
    {
        $this->elastic->update([
            'index' => $this->elastic_index,
            'type' => ET::ASSESSMENT,
            'id' => $data['_id'],
            'body' => [
                'doc' => $data['body']
            ]
        ]);
        Log::info("Quiz(" . $data['body']['id'] . ") " . $data['body']['title'] . " indexed");
    }

    /**
     * {@inheritdoc}
     */
    public function removeQuiz($_id)
    {
        $this->elastic->delete([
            'index' => $this->elastic_index,
            'type' => ET::ASSESSMENT,
            'id' => $_id
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function quizUsers($quiz)
    {
        $this->elastic->update([
            'index' => $this->elastic_index,
            'type' => ET::ASSESSMENT,
            'id' => $quiz->_id,
            'body' => [
                'doc' => [
                    'user_ids' => $quiz->users
                ]
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function addEvent($data)
    {
        $this->elastic->index([
            'index' => $this->elastic_index,
            'type' => ET::EVENT,
            'id' => $data['_id'],
            'body' => $data['body']
        ]);
        Log::info("Quiz(" . $data['body']['id'] . ") " . $data['body']['title'] . " indexed");
    }

    /**
     * {@inheritdoc}
     */
    public function updateEvent($data)
    {
        $this->elastic->update([
            'index' => $this->elastic_index,
            'type' => ET::EVENT,
            'id' => $data['_id'],
            'body' => [
                'doc' => $data['body']
            ]
        ]);
        Log::info("Quiz(" . $data['body']['id'] . ") " . $data['body']['title'] . " indexed");
    }

    /**
     * {@inheritdoc}
     */
    public function removeEvent($_id)
    {
        try {
            $this->elastic->delete([
                'index' => $this->elastic_index,
                'type' => ET::EVENT,
                'id' => $_id
            ]);
        } catch (Exception $e) {
            Log::info('Event is not indexed event id ' .$_id . " Message :: ".$e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function eventUsers($event)
    {
        $this->elastic->update([
            'index' => $this->elastic_index,
            'type' => ET::EVENT,
            'id' => $event->_id,
            'body' => [
                'doc' => [
                    'user_ids' => $event->users
                ]
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function AssignmentUsers($assignment)
    {
        $this->elastic->update([
            'index' => $this->elastic_index,
            'type' => ET::ASSIGNMENT,
            'id' => $assignment->_id,
            'body' => [
                'doc' => [
                    'user_ids' => $assignment->users
                ]
            ]
        ]);
    }
}
