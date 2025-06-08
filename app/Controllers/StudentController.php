<?php

namespace App\Controllers;

use App\Models\StudentModel;

class StudentController extends BaseController
{
    protected $StudentModel;

    public function __construct()
    {
        $this->StudentModel = new StudentModel();
    }

    public function index()
    {
        $data['title'] = 'students';
        $data['students'] = $this->StudentModel->findAll();
        
        return view('students/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Create Student';
        
        if ($this->request->getMethod() === 'post') {
            $rules = [
                // Add your validation rules here
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $this->StudentModel->save($this->request->getPost());
                return redirect()->to('/students')->with('message', 'Student created successfully');
            }
        }

        return view('students/create', $data);
    }

    public function edit($id = null)
    {
        $data['title'] = 'Edit Student';
        $data['Student'] = $this->StudentModel->find($id);

        if (empty($data['Student'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the Student with id: ' . $id);
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                // Add your validation rules here
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $this->StudentModel->update($id, $this->request->getPost());
                return redirect()->to('/students')->with('message', 'Student updated successfully');
            }
        }

        return view('students/edit', $data);
    }

    public function delete($id = null)
    {
        if ($this->StudentModel->delete($id)) {
            return redirect()->to('/students')->with('message', 'Student deleted successfully');
        }
        
        return redirect()->to('/students')->with('error', 'Failed to delete Student');
    }

    public function view($id = null)
    {
        $data['title'] = 'View Student';
        $data['Student'] = $this->StudentModel->find($id);

        if (empty($data['Student'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the Student with id: ' . $id);
        }

        return view('students/view', $data);
    }
}