<?php

namespace App\Transformers\Package;

class PackageProgramTransformer
{
    public function transformForPackageProgramList($programs)
    {
        return $programs->map(function ($program) {
            return [
                'id' => $program->program_id,
                'title' => $program->program_title,
                'slug' => $program->program_slug,
                'posts' => $program->posts()->filter([])->count(),
                'elements' => $this->getElementsCount($program),
                'created_at' => $program->created_at->timestamp,
                'created_by' => $program->created_by,
                'status' => $program->status,
            ];
        })->all();
    }

    /**
     * @param \App\Model\Program $program
     * @return int
     */
    private function getElementsCount($program)
    {
        return $program->posts()->filter([])->get()
            ->pluck("elements")->collapse()->count();
    }
}
