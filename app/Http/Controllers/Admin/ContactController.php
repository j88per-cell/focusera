<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::orderByDesc('created_at')->paginate(20);
        return inertia('Contacts/Index', compact('messages'))->rootView('admin');
    }

    public function show(ContactMessage $contact)
    {
        if ($contact->status === 'new') {
            $contact->update(['status' => 'read', 'read_at' => now()]);
        }
        return inertia('Contacts/Show', ['message' => $contact])->rootView('admin');
    }

    public function update(Request $request, ContactMessage $contact)
    {
        $data = $request->validate([
            'status' => 'required|in:new,read,archived',
        ]);
        $contact->update($data);
        return redirect()->route('admin.contacts.show', $contact);
    }

    public function destroy(ContactMessage $contact)
    {
        $contact->delete();
        return redirect()->route('admin.contacts.index');
    }
}
