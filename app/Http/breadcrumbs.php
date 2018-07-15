<?php

Breadcrumbs::register("admin-dashboard", function ($breadcrumbs) {
    $breadcrumbs->push("Dashboard", URL::to("cp"));
});

Breadcrumbs::register("admin-question", function ($breadcrumbs) {
    $breadcrumbs->parent("admin-dashboard");
    $breadcrumbs->push("Manage Question Bank", URL::to("cp/assessment/list-questionbank"));
});

Breadcrumbs::register("admin-add-question", function ($breadcrumbs) {
    $breadcrumbs->parent("admin-question");
    $breadcrumbs->push("Add Question", null);
});

Breadcrumbs::register("admin-edit-question", function ($breadcrumbs) {
    $breadcrumbs->parent("admin-question");
    $breadcrumbs->push("Edit Question", null);
});
