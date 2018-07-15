<?php

namespace App\Enums\HomePage;

use App\Enums\BaseEnum;

abstract class HomePagePermission extends BaseEnum
{
    const LIST_BANNERS = "list_banners";

    const ADD_BANNERS = "add-banners";

    const EDIT_BANNERS = "edit-banners";

    const DELETE_BANNERS = "delete-banners";

    const LIST_PARTNER = "list-partner";

    const ADD_PARTNER = "add-partner";

    const EDIT_PARTNER = "edit-partner";

    const DELETE_PARTNER = "delete-partner";

    const LIST_UPCOMING_COURSES = "list-upcoming-courses";

    const ADD_UPCOMING_COURSES = "add-upcoming-courses";

    const DELETE_UPCOMING_COURSES = "delete-upcoming-courses";

    const LIST_POPULAR_COURSES = "list-popular-courses";

    const ADD_POPULAR_COURSES = "add-popular-courses";

    const DELETE_POPULAR_COURSES = "delete-popular-courses";

    const LIST_TESTIMONIALS = "list_testimonials";

    const ADD_TESTIMONIALS = "add-testimonials";

    const EDIT_TESTIMONIALS = "edit-testimonials";

    const DELETE_TESTIMONIALS = "delete-testimonials";
}
