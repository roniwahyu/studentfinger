<?php

namespace App\Controllers;

use App\Models\TestTableModel;

class TestTableController extends BaseController
{
    protected $TestTableModel;

    public function __construct()
    {
        $this->TestTableModel = new TestTableModel();
    }

    public function index()
    {
        $data['title'] = 'testtables';
        $data['testtables'] = $this->TestTableModel->findAll();
        
        return view('testtables/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Create TestTable';
        
        if ($this->request->getMethod() === 'post') {
            $rules = [
                // Add your validation rules here
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $this->TestTableModel->save($this->request->getPost());
                return redirect()->to('/testtables')->with('message', 'TestTable created successfully');
            }
        }

        return view('testtables/create', $data);
    }

    public function edit($id = null)
    {
        $data['title'] = 'Edit TestTable';
        $data['TestTable'] = $this->TestTableModel->find($id);

        if (empty($data['TestTable'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the TestTable with id: ' . $id);
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                // Add your validation rules here
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $this->TestTableModel->update($id, $this->request->getPost());
                return redirect()->to('/testtables')->with('message', 'TestTable updated successfully');
            }
        }

        return view('testtables/edit', $data);
    }

    public function delete($id = null)
    {
        if ($this->TestTableModel->delete($id)) {
            return redirect()->to('/testtables')->with('message', 'TestTable deleted successfully');
        }
        
        return redirect()->to('/testtables')->with('error', 'Failed to delete TestTable');
    }

    public function view($id = null)
    {
        $data['title'] = 'View TestTable';
        $data['TestTable'] = $this->TestTableModel->find($id);

        if (empty($data['TestTable'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the TestTable with id: ' . $id);
        }

        return view('testtables/view', $data);
    }
}