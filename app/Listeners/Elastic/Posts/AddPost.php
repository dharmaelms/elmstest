<?php

namespace App\Listeners\Elastic\Posts;

use App\Events\Elastic\Posts\PostAdded;
use App\Jobs\Elastic\Posts\IndexPost;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddPost implements ShouldQueue
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
     * @param PostAdded $post
     * @return void
     */
    public function handle(PostAdded $post)
    {
        dispatch(new IndexPost($post->post_id));
    }
}
