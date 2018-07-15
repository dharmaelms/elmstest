<?php

namespace App\Listeners\Elastic\Posts;

use App\Events\Elastic\Posts\PostRemoved;
use App\Jobs\Elastic\Posts\DeletePost;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemovePost implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PostRemoved $post
     * @return void
     */
    public function handle(PostRemoved $post)
    {
        dispatch(new DeletePost($post->post_id));
    }
}
