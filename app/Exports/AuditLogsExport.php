<?php

namespace App\Exports;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class AuditLogsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function query() 
    {
        $query = AuditLog::with('user')->latest();  
        
         if ($this->request->filled('user_id')) {
            $query->where('user_id', $this->request->user_id);
        }

        if ($this->request->filled('action')) {
            $query->where('action', $this->request->action);
        }

        if ($this->request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $this->request->date_from);
        }

        if ($this->request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $this->request->date_to);
        }

        if ($this->request->filled('search')) {
            $query->where('description', 'like', '%'.$this->request->search.'%');
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'User',
            'Action',
            'Description',
            'Model',
            'IP Address',
            'User Agent',
            'URL',
            'Method',
            'Date & Time'
        ];    
    }

    public function map($log) : array {
        return [
            $log->id,
            $log->user ? $log->user->name : 'System',
            $log->action,
            $log->description,
            $log->model_type,
            $log->model_id,
            $log->ip_address,
            $log->user_agent,
            $log->url,
            $log->method,
            $log->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)  {
        return [
            1 => ['font' => ['bold' => true]]
        ];  
    }
}