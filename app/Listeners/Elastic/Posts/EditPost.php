<?php

namespace App\Listeners\Elastic\Posts;

use App\Events\Elastic\Posts\PostEdited;
use App\Jobs\Elastic\Posts\IndexPost;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EditPost implements ShouldQueue
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
     * @param  PostEdited $post
     * @return void
     */
    public function handle(PostEdited $post)
    {
        dispatch(new IndexPost($post->post_id, $post->is_slug_changed, false));
    }
}
