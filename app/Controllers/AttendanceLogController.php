<?php

namespace App\Controllers;

use App\Models\AttendanceLogModel;

class AttendanceLogController extends BaseController
{
    protected $AttendanceLogModel;

    public function __construct()
    {
        $this->AttendanceLogModel = new AttendanceLogModel();
    }

    public function index()
    {
        $data['title'] = 'Attendance Logs';

        // Get filters from request
        $filters = [
            'search' => $this->request->getGet('search'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'verifymode' => $this->request->getGet('verifymode'),
            'inoutmode' => $this->request->getGet('inoutmode'),
            'sn' => $this->request->getGet('sn')
        ];

        // Get attendance logs with pagination
        $data['attendancelogs'] = $this->AttendanceLogModel->getLogsWithStudentsPaginated($filters, 20, 'default');
        $data['pager'] = $this->AttendanceLogModel->pager;
        $data['filters'] = $filters;

        return view('attendancelogs/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Create Attendance Log';

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'sn' => 'required|max_length[30]',
                'scan_date' => 'required|valid_date[Y-m-d H:i:s]',
                'pin' => 'required|max_length[32]',
                'verifymode' => 'required|integer',
                'inoutmode' => 'permit_empty|integer',
                'reserved' => 'permit_empty|integer',
                'work_code' => 'permit_empty|integer',
                'att_id' => 'permit_empty|max_length[50]'
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $this->AttendanceLogModel->save($this->request->getPost());
                return redirect()->to('/attendance-logs')->with('message', 'Attendance log created successfully');
            }
        }

        return view('attendancelogs/create', $data);
    }

    public function edit($encodedKey = null)
    {
        $data['title'] = 'Edit Attendance Log';

        // Decode the composite key (sn|scan_date|pin)
        $keyParts = explode('|', base64_decode($encodedKey));
        if (count($keyParts) !== 3) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid attendance log key');
        }

        $data['AttendanceLog'] = $this->AttendanceLogModel
            ->where('sn', $keyParts[0])
            ->where('scan_date', $keyParts[1])
            ->where('pin', $keyParts[2])
            ->first();

        if (empty($data['AttendanceLog'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the attendance log');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'verifymode' => 'required|integer',
                'inoutmode' => 'permit_empty|integer',
                'reserved' => 'permit_empty|integer',
                'work_code' => 'permit_empty|integer',
                'att_id' => 'permit_empty|max_length[50]'
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                // Update the record using composite key
                $this->AttendanceLogModel
                    ->where('sn', $keyParts[0])
                    ->where('scan_date', $keyParts[1])
                    ->where('pin', $keyParts[2])
                    ->set($this->request->getPost())
                    ->update();
                return redirect()->to('/attendance-logs')->with('message', 'Attendance log updated successfully');
            }
        }

        return view('attendancelogs/edit', $data);
    }

    public function delete($encodedKey = null)
    {
        // Decode the composite key (sn|scan_date|pin)
        $keyParts = explode('|', base64_decode($encodedKey));
        if (count($keyParts) !== 3) {
            return redirect()->to('/attendance-logs')->with('error', 'Invalid attendance log key');
        }

        $deleted = $this->AttendanceLogModel
            ->where('sn', $keyParts[0])
            ->where('scan_date', $keyParts[1])
            ->where('pin', $keyParts[2])
            ->delete();

        if ($deleted) {
            return redirect()->to('/attendance-logs')->with('message', 'Attendance log deleted successfully');
        }

        return redirect()->to('/attendance-logs')->with('error', 'Failed to delete attendance log');
    }

    public function view($encodedKey = null)
    {
        $data['title'] = 'View Attendance Log';

        // Decode the composite key (sn|scan_date|pin)
        $keyParts = explode('|', base64_decode($encodedKey));
        if (count($keyParts) !== 3) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid attendance log key');
        }

        $data['AttendanceLog'] = $this->AttendanceLogModel
            ->where('sn', $keyParts[0])
            ->where('scan_date', $keyParts[1])
            ->where('pin', $keyParts[2])
            ->first();

        if (empty($data['AttendanceLog'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the attendance log');
        }

        return view('attendancelogs/view', $data);
    }
}