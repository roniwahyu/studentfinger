<?php

namespace App\Controllers;

use App\Models\UserLogModel;

class UserLogController extends BaseController
{
    protected $UserLogModel;

    public function __construct()
    {
        $this->UserLogModel = new UserLogModel();
    }

    public function index()
    {
        $data['title'] = 'User Logs';

        // Get filters from request
        $filters = [
            'search' => $this->request->getGet('search'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'module' => $this->request->getGet('module'),
            'tipe_log' => $this->request->getGet('tipe_log'),
            'login_id' => $this->request->getGet('login_id')
        ];

        // Get user logs with pagination
        $userLogsBuilder = $this->UserLogModel->getUserLogsWithFilters($filters);
        $data['userlogs'] = $userLogsBuilder->paginate(20);
        $data['pager'] = $this->UserLogModel->pager;
        $data['filters'] = $filters;

        return view('userlogs/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Create User Log';

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'login_id' => 'required|max_length[50]',
                'log_date' => 'required|valid_date[Y-m-d H:i:s]',
                'module' => 'required|integer|in_list[0,1,2,3,4,5]',
                'tipe_log' => 'required|integer|in_list[0,1,2,3]',
                'nama_data' => 'permit_empty|max_length[250]',
                'log_note' => 'required|max_length[300]'
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $this->UserLogModel->save($this->request->getPost());
                return redirect()->to('/user-logs')->with('message', 'User log created successfully');
            }
        }

        return view('userlogs/create', $data);
    }

    public function edit($encodedKey = null)
    {
        $data['title'] = 'Edit User Log';

        // Decode the composite key (login_id|log_date)
        $keyParts = explode('|', base64_decode($encodedKey));
        if (count($keyParts) !== 2) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid user log key');
        }

        $data['UserLog'] = $this->UserLogModel
            ->where('login_id', $keyParts[0])
            ->where('log_date', $keyParts[1])
            ->first();

        if (empty($data['UserLog'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the user log');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'module' => 'required|integer|in_list[0,1,2,3,4,5]',
                'tipe_log' => 'required|integer|in_list[0,1,2,3]',
                'nama_data' => 'permit_empty|max_length[250]',
                'log_note' => 'required|max_length[300]'
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                // Update the record using composite key
                $this->UserLogModel
                    ->where('login_id', $keyParts[0])
                    ->where('log_date', $keyParts[1])
                    ->set($this->request->getPost())
                    ->update();
                return redirect()->to('/user-logs')->with('message', 'User log updated successfully');
            }
        }

        return view('userlogs/edit', $data);
    }

    public function delete($encodedKey = null)
    {
        // Decode the composite key (login_id|log_date)
        $keyParts = explode('|', base64_decode($encodedKey));
        if (count($keyParts) !== 2) {
            return redirect()->to('/user-logs')->with('error', 'Invalid user log key');
        }

        $deleted = $this->UserLogModel
            ->where('login_id', $keyParts[0])
            ->where('log_date', $keyParts[1])
            ->delete();

        if ($deleted) {
            return redirect()->to('/user-logs')->with('message', 'User log deleted successfully');
        }

        return redirect()->to('/user-logs')->with('error', 'Failed to delete user log');
    }

    public function view($encodedKey = null)
    {
        $data['title'] = 'View User Log';

        // Decode the composite key (login_id|log_date)
        $keyParts = explode('|', base64_decode($encodedKey));
        if (count($keyParts) !== 2) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid user log key');
        }

        $data['UserLog'] = $this->UserLogModel
            ->where('login_id', $keyParts[0])
            ->where('log_date', $keyParts[1])
            ->first();

        if (empty($data['UserLog'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the user log');
        }

        return view('userlogs/view', $data);
    }
}