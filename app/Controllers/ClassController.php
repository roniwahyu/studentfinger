<?php

namespace App\Controllers;

use App\Models\ClassModel;

class ClassController extends BaseController
{
    protected $ClassModel;

    public function __construct()
    {
        $this->ClassModel = new ClassModel();
    }

    public function index()
    {
        $data['title'] = 'classes';
        $data['classes'] = $this->ClassModel->findAll();
        
        return view('classes/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Create Class';
        
        if ($this->request->getMethod() === 'post') {
            $rules = [
                // Add your validation rules here
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $this->ClassModel->save($this->request->getPost());
                return redirect()->to('/classes')->with('message', 'Class created successfully');
            }
        }

        return view('classes/create', $data);
    }

    public function edit($id = null)
    {
        $data['title'] = 'Edit Class';
        $data['Class'] = $this->ClassModel->find($id);

        if (empty($data['Class'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the Class with id: ' . $id);
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                // Add your validation rules here
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $this->ClassModel->update($id, $this->request->getPost());
                return redirect()->to('/classes')->with('message', 'Class updated successfully');
            }
        }

        return view('classes/edit', $data);
    }

    public function delete($id = null)
    {
        if ($this->ClassModel->delete($id)) {
            return redirect()->to('/classes')->with('message', 'Class deleted successfully');
        }
        
        return redirect()->to('/classes')->with('error', 'Failed to delete Class');
    }

    public function view($id = null)
    {
        $data['title'] = 'View Class';
        $data['Class'] = $this->ClassModel->find($id);

        if (empty($data['Class'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the Class with id: ' . $id);
        }

        return view('classes/view', $data);
    }
}