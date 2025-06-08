<?php

namespace App\Controllers;

use App\Models\AttLogModel;

class AttLogController extends BaseController
{
    protected $AttLogModel;

    public function __construct()
    {
        $this->AttLogModel = new AttLogModel();
    }

    public function index()
    {
        $data['title'] = 'attlogs';
        $data['attlogs'] = $this->AttLogModel->findAll();
        
        return view('attlogs/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Create AttLog';
        
        if ($this->request->getMethod() === 'post') {
            $rules = [
                // Add your validation rules here
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $this->AttLogModel->save($this->request->getPost());
                return redirect()->to('/attlogs')->with('message', 'AttLog created successfully');
            }
        }

        return view('attlogs/create', $data);
    }

    public function edit($id = null)
    {
        $data['title'] = 'Edit AttLog';
        $data['AttLog'] = $this->AttLogModel->find($id);

        if (empty($data['AttLog'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the AttLog with id: ' . $id);
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                // Add your validation rules here
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $this->AttLogModel->update($id, $this->request->getPost());
                return redirect()->to('/attlogs')->with('message', 'AttLog updated successfully');
            }
        }

        return view('attlogs/edit', $data);
    }

    public function delete($id = null)
    {
        if ($this->AttLogModel->delete($id)) {
            return redirect()->to('/attlogs')->with('message', 'AttLog deleted successfully');
        }
        
        return redirect()->to('/attlogs')->with('error', 'Failed to delete AttLog');
    }

    public function view($id = null)
    {
        $data['title'] = 'View AttLog';
        $data['AttLog'] = $this->AttLogModel->find($id);

        if (empty($data['AttLog'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the AttLog with id: ' . $id);
        }

        return view('attlogs/view', $data);
    }
}