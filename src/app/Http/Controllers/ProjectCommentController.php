<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectCommentController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $project->comments()->create([
            'company_id' => $project->company_id,
            'user_id'    => $request->user()->id,
            'body'       => $data['body'],
        ]);

        return back()->with('status', 'Commentaire ajouté.');
    }
}
