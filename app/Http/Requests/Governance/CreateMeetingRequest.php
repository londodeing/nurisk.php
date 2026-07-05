<?php

namespace App\Http\Requests\Governance;

use Illuminate\Foundation\Http\FormRequest;

class CreateMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'meeting_type' => ['required', 'in:pleno,rapat_kerja,rapat_koordinasi,rapat_darurat,khusus'],
            'node_id' => ['required', 'integer', 'exists:org_nodes,id'],
            'chairperson_mandate_id' => ['required', 'integer', 'exists:org_mandates,id'],
            'secretary_mandate_id' => ['required', 'integer', 'exists:org_mandates,id'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'venue' => ['nullable', 'string', 'max:255'],
            'venue_type' => ['nullable', 'in:offline,online,hybrid'],
            'quorum_required' => ['nullable', 'integer', 'min:0'],
            'related_incident_id' => ['nullable', 'integer', 'exists:operasi_insiden,id_insiden'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul rapat wajib diisi.',
            'meeting_type.required' => 'Jenis rapat wajib dipilih.',
            'node_id.required' => 'Unit organisasi wajib dipilih.',
            'chairperson_mandate_id.required' => 'Pimpinan rapat wajib ditentukan.',
            'secretary_mandate_id.required' => 'Notulis rapat wajib ditentukan.',
        ];
    }
}
