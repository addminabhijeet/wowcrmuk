<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function edit($id)
    {
        $template = EmailTemplate::find($id);

        if (!$template) {
            return redirect()->back()->with('error', 'Template not found');
        }

        return view('smtp.edittemplate', compact('template'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $template = EmailTemplate::findOrFail($id);
        $template->update($request->only('subject', 'body'));

        return redirect()->back()->with('success', 'Template updated successfully!');
    }

    public function renderTemplate($slug, $data = [])
    {
        $template = EmailTemplate::where('name', $slug)->first();
        if (!$template) {
            return null;
        }

        $body = $template->body;
        foreach ($data as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        return [
            'subject' => $template->subject,
            'body' => $body
        ];
    }
}
