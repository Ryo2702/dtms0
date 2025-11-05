<?php

namespace App\Http\Controllers\Admin\Departments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\DepartmentRequest;
use App\Models\Department;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    use AuthorizesRequests;

    public function index(){
        $departments = Department::paginate(8);
        return view('admin.departments.index', compact('departments'));
    }

    public function store(DepartmentRequest $request) {
        try{

            $code = $this->generateCodeTitle($request->input('title'));

            $data = [
                'name' => $request->input('title'),
                'code' => $code,
                'description' => $request->input('description'),
                'status' => $request->has('status') ? 1 : 0
            ];

            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('departments/logos', 'public');
                $data['logo'] = $logoPath;
            }

            Department::create($data);

            return redirect()
                ->route('admin.departments.index')
                ->with('success', 'Department created successfully.');
        }catch(\Exception $e){
            Log::error('Department creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Department creation failed.');
        }
    }

    public function edit($id) {
        $department = Department::findOrFail($id);
        return response()->json($department);
    }

    public function update(DepartmentRequest $request, $id) {
        try{
            $department = Department::findOrFail($id);

            $code = $this->generateCodeTitle($request->input('title'));
            $data = [
                'name' => $request->input('title'),
                'code' => $code,
                'description' => $request->input('description'),
                'status' => $request->has('status') ? 1 : 0
            ];

            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('departments/logos', 'public');
                $data['logo'] = $logoPath;
            }

            $department->update();

            return redirect()
                ->route('admin.departments.index')
                ->with('success', 'Department updated successfully');
        }catch(\Exception $e){
            Log::error('Department update failed' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Department update failed');
        }
    }

    private function generateCodeTitle(string $title): string
    {
        $words = explode(' ', $title);
        $code = '';

        foreach ($words as $word) {
           if (!empty($word)) {
             $code .= strtoupper(substr($word, 0, 1));
           }
        }

        return $code ?: 'DEPT';
    }

}
