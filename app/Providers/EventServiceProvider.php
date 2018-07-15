<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Auth\Registered' => [
            'App\Listeners\Auth\AssignRole'
        ],
        'App\Events\Auth\UpdateUserRole' => [
            'App\Listeners\Auth\UpdateRole',
        ],
        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\SaveUserSessionData',
        ],
        'Illuminate\Auth\Events\Logout' => [
            'App\Listeners\CleanupUserSessionData',
        ],
        'App\Events\DAMs\DocumentAdded' => [
            'App\Listeners\DAMs\AddDocumentHandler'
        ],
        'App\Events\DAMs\DocumentUpdated' => [
            'App\Listeners\DAMs\UpdateDocumentHandler'
        ],
        'App\Events\DAMs\DocumentDeleted' => [
            'App\Listeners\DAMs\DeleteDocumentHandler'
        ],
        'App\Events\Elastic\Programs\ProgramAdded' => [
            'App\Listeners\Elastic\Programs\AddProgram'
        ],
        'App\Events\Elastic\Programs\ProgramUpdated' => [
            'App\Listeners\Elastic\Programs\EditProgram'
        ],
        'App\Events\Elastic\Programs\ProgramRemoved' => [
            'App\Listeners\Elastic\Programs\RemoveProgram'
        ],
        'App\Events\Elastic\Packages\PackageAdded' => [
            'App\Listeners\Elastic\Packages\AddPackage'
        ],
        'App\Events\Elastic\Packages\PackageEdited' => [
            'App\Listeners\Elastic\Packages\EditPackage'
        ],
        'App\Events\Elastic\Packages\PackageRemoved' => [
            'App\Listeners\Elastic\Packages\RemovePackage'
        ],
        'App\Events\Elastic\Posts\PostAdded' => [
            'App\Listeners\Elastic\Posts\AddPost'
        ],
        'App\Events\Elastic\Posts\PostEdited' => [
            'App\Listeners\Elastic\Posts\EditPost'
        ],
        'App\Events\Elastic\Posts\PostRemoved' => [
            'App\Listeners\Elastic\Posts\RemovePost'
        ],
        'App\Events\Elastic\Items\ItemsAdded' => [
            'App\Listeners\Elastic\Items\AddItems'
        ],
        'App\Events\Elastic\Users\UsersAssigned' => [
            'App\Listeners\Elastic\Users\AddUser'
        ],
        'App\Events\Elastic\Users\UserGroupAssigned' => [
            'App\Listeners\Elastic\Users\AddUserGroup'
        ],
        'App\Events\Elastic\Users\PackageAssigned' => [
            'App\Listeners\Elastic\Users\AddPackage'
        ],
        'App\Events\Elastic\Quizzes\QuizAdded' => [
            'App\Listeners\Elastic\Quizzes\AddQuiz'
        ],
        'App\Events\Elastic\Quizzes\QuizAssigned' => [
            'App\Listeners\Elastic\Quizzes\AssignUser'
        ],
        'App\Events\Elastic\Quizzes\QuizEdited' => [
            'App\Listeners\Elastic\Quizzes\EditQuiz'
        ],
        'App\Events\Elastic\Quizzes\QuizRemoved' => [
            'App\Listeners\Elastic\Quizzes\RemoveQuiz'
        ],
        'App\Events\Elastic\Events\EventAdded' => [
            'App\Listeners\Elastic\Events\AddEvent'
        ],
        'App\Events\Elastic\Events\EventAssigned' => [
            'App\Listeners\Elastic\Events\AssignUser'
        ],
        'App\Events\Elastic\Events\EventEdited' => [
            'App\Listeners\Elastic\Events\EditEvent'
        ],
        'App\Events\Elastic\Events\EventRemoved' => [
            'App\Listeners\Elastic\Events\RemoveEvent'
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        'App\Listeners\User\EnrollEntity',
        'App\Listeners\User\UnenrollEntity',
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
    }
}
