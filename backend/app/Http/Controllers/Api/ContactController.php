<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $contacts = Contact::query()
            ->when($request->string('search')->trim()->value(), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(min($request->integer('per_page', 25), 100));

        return response()->json($contacts);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'workspace_id' => ['nullable', 'integer'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'company' => ['nullable', 'string', 'max:160'],
            'job_title' => ['nullable', 'string', 'max:160'],
            'source' => ['nullable', 'string', 'max:80'],
        ]);

        $contact = Contact::create($data);

        return response()->json(['contact' => $contact], 201);
    }

    public function show(Contact $contact): JsonResponse
    {
        return response()->json(['contact' => $contact]);
    }

    public function update(Request $request, Contact $contact): JsonResponse
    {
        $data = $request->validate([
            'workspace_id' => ['nullable', 'integer'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'company' => ['nullable', 'string', 'max:160'],
            'job_title' => ['nullable', 'string', 'max:160'],
            'source' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'in:active,inactive,blocked'],
        ]);

        $contact->update($data);
        return response()->json(['contact' => $contact->fresh()]);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();
        return response()->json(['message' => 'Contact deleted.']);
    }
}
