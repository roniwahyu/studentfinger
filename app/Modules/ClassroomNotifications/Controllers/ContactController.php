<?php

namespace App\Modules\ClassroomNotifications\Controllers;

use App\Controllers\BaseController;
use App\Modules\ClassroomNotifications\Models\ParentContactModel;
use App\Modules\ClassroomNotifications\Models\WhatsAppConnectionModel;

/**
 * Contact Controller
 * 
 * Manages parent contacts for classroom notifications
 */
class ContactController extends BaseController
{
    protected $contactModel;
    protected $connectionModel;
    
    public function __construct()
    {
        $this->contactModel = new ParentContactModel();
        $this->connectionModel = new WhatsAppConnectionModel();
    }
    
    /**
     * Contact management dashboard
     */
    public function index()
    {
        $filters = [
            'contact_type' => $this->request->getGet('contact_type'),
            'is_active' => $this->request->getGet('is_active'),
            'search' => $this->request->getGet('search')
        ];
        
        $data = [
            'title' => 'Contact Management',
            'contacts' => $this->contactModel->getContactsWithStudents(50, 0, $filters),
            'filters' => $filters,
            'stats' => $this->contactModel->getContactStats(),
            'connection_status' => $this->connectionModel->getCurrentStatus()
        ];
        
        return view('App\Modules\ClassroomNotifications\Views\contacts', $data);
    }
    
    /**
     * Create new contact
     */
    public function create()
    {
        $data = [
            'title' => 'Add New Contact',
            'students' => $this->getStudents(),
            'contact_types' => [
                'father' => 'Father',
                'mother' => 'Mother',
                'guardian' => 'Guardian',
                'emergency' => 'Emergency Contact'
            ]
        ];
        
        return view('App\Modules\ClassroomNotifications\Views\contact_form', $data);
    }
    
    /**
     * Save contact
     */
    public function save()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'student_id' => 'required|integer',
            'contact_type' => 'required|in_list[father,mother,guardian,emergency]',
            'contact_name' => 'required|min_length[2]|max_length[100]',
            'phone_number' => 'required|min_length[10]|max_length[20]',
            'whatsapp_number' => 'permit_empty|min_length[10]|max_length[20]',
            'email' => 'permit_empty|valid_email'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        // Clean phone numbers
        $phoneNumber = $this->contactModel->cleanPhoneNumber($this->request->getPost('phone_number'));
        $whatsappNumber = $this->request->getPost('whatsapp_number');
        if ($whatsappNumber) {
            $whatsappNumber = $this->contactModel->cleanPhoneNumber($whatsappNumber);
        }
        
        // Validate phone numbers
        if (!$this->contactModel->validatePhoneNumber($phoneNumber)) {
            return redirect()->back()->withInput()->with('error', 'Invalid phone number format');
        }
        
        if ($whatsappNumber && !$this->contactModel->validatePhoneNumber($whatsappNumber)) {
            return redirect()->back()->withInput()->with('error', 'Invalid WhatsApp number format');
        }
        
        $data = [
            'student_id' => $this->request->getPost('student_id'),
            'contact_type' => $this->request->getPost('contact_type'),
            'contact_name' => $this->request->getPost('contact_name'),
            'phone_number' => $phoneNumber,
            'whatsapp_number' => $whatsappNumber ?: $phoneNumber,
            'email' => $this->request->getPost('email'),
            'relationship' => $this->request->getPost('relationship'),
            'is_primary' => $this->request->getPost('is_primary') ? 1 : 0,
            'is_active' => 1,
            'receive_notifications' => $this->request->getPost('receive_notifications') ? 1 : 0,
            'notes' => $this->request->getPost('notes')
        ];
        
        // Handle primary contact logic
        if ($data['is_primary']) {
            $this->contactModel->setPrimaryContact($data['student_id'], 0); // Remove existing primary
        }
        
        if ($this->contactModel->insert($data)) {
            return redirect()->to('classroom-notifications/contacts')->with('success', 'Contact added successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to add contact');
        }
    }
    
    /**
     * Edit contact
     */
    public function edit(int $contactId)
    {
        $contact = $this->contactModel->find($contactId);
        if (!$contact) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Contact not found');
        }
        
        $data = [
            'title' => 'Edit Contact',
            'contact' => $contact,
            'students' => $this->getStudents(),
            'contact_types' => [
                'father' => 'Father',
                'mother' => 'Mother',
                'guardian' => 'Guardian',
                'emergency' => 'Emergency Contact'
            ]
        ];
        
        return view('App\Modules\ClassroomNotifications\Views\contact_form', $data);
    }
    
    /**
     * Update contact
     */
    public function update(int $contactId)
    {
        $contact = $this->contactModel->find($contactId);
        if (!$contact) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Contact not found');
        }
        
        $validation = \Config\Services::validation();
        
        $rules = [
            'contact_name' => 'required|min_length[2]|max_length[100]',
            'phone_number' => 'required|min_length[10]|max_length[20]',
            'whatsapp_number' => 'permit_empty|min_length[10]|max_length[20]',
            'email' => 'permit_empty|valid_email'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        // Clean phone numbers
        $phoneNumber = $this->contactModel->cleanPhoneNumber($this->request->getPost('phone_number'));
        $whatsappNumber = $this->request->getPost('whatsapp_number');
        if ($whatsappNumber) {
            $whatsappNumber = $this->contactModel->cleanPhoneNumber($whatsappNumber);
        }
        
        $data = [
            'contact_name' => $this->request->getPost('contact_name'),
            'phone_number' => $phoneNumber,
            'whatsapp_number' => $whatsappNumber ?: $phoneNumber,
            'email' => $this->request->getPost('email'),
            'relationship' => $this->request->getPost('relationship'),
            'is_primary' => $this->request->getPost('is_primary') ? 1 : 0,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'receive_notifications' => $this->request->getPost('receive_notifications') ? 1 : 0,
            'notes' => $this->request->getPost('notes')
        ];
        
        // Handle primary contact logic
        if ($data['is_primary'] && !$contact['is_primary']) {
            $this->contactModel->setPrimaryContact($contact['student_id'], $contactId);
        }
        
        if ($this->contactModel->update($contactId, $data)) {
            return redirect()->to('classroom-notifications/contacts')->with('success', 'Contact updated successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update contact');
        }
    }
    
    /**
     * Delete contact
     */
    public function delete(int $contactId)
    {
        $contact = $this->contactModel->find($contactId);
        if (!$contact) {
            return redirect()->back()->with('error', 'Contact not found');
        }
        
        if ($this->contactModel->delete($contactId)) {
            return redirect()->back()->with('success', 'Contact deleted successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to delete contact');
        }
    }
    
    /**
     * Set primary contact
     */
    public function setPrimary()
    {
        $contactId = $this->request->getPost('contact_id');
        $studentId = $this->request->getPost('student_id');
        
        if ($this->contactModel->setPrimaryContact($studentId, $contactId)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Primary contact updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update primary contact'
            ]);
        }
    }
    
    /**
     * Get contacts by student (AJAX)
     */
    public function getContactsByStudent()
    {
        $studentId = $this->request->getPost('student_id');
        
        if (empty($studentId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Student ID is required'
            ]);
        }
        
        $contacts = $this->contactModel->getContactsByStudent($studentId);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $contacts
        ]);
    }
    
    /**
     * Search contacts (AJAX)
     */
    public function searchContacts()
    {
        $query = $this->request->getGet('q');
        
        if (empty($query) || strlen($query) < 2) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Search query must be at least 2 characters'
            ]);
        }
        
        $contacts = $this->contactModel->searchContacts($query);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $contacts
        ]);
    }
    
    /**
     * Bulk update notification preferences
     */
    public function bulkUpdatePreferences()
    {
        $contactIds = $this->request->getPost('contact_ids');
        $preferences = $this->request->getPost('preferences');
        
        if (empty($contactIds) || !is_array($contactIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No contacts selected'
            ]);
        }
        
        if ($this->contactModel->bulkUpdateNotificationPreferences($contactIds, $preferences)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Notification preferences updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update notification preferences'
            ]);
        }
    }
    
    /**
     * Import contacts from CSV
     */
    public function importContacts()
    {
        // Implementation for CSV import
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Import functionality will be implemented'
        ]);
    }
    
    /**
     * Export contacts to CSV
     */
    public function exportContacts()
    {
        // Implementation for CSV export
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Export functionality will be implemented'
        ]);
    }
    
    /**
     * Send bulk message to selected contacts
     */
    public function sendBulkMessage()
    {
        $contactIds = $this->request->getPost('contact_ids');
        $message = $this->request->getPost('message');
        $logMessage = $this->request->getPost('log_message');

        if (empty($contactIds) || !is_array($contactIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No contacts selected'
            ]);
        }

        if (empty($message)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Message is required'
            ]);
        }

        // Load WhatsApp service
        $whatsappService = new \App\Modules\ClassroomNotifications\Services\WhatsAppService();

        $sessionData = [];
        if ($logMessage) {
            $sessionData = [
                'session_id' => 0,
                'event_type' => 'bulk_message'
            ];
        }

        $result = $whatsappService->sendToSpecificContacts($contactIds, $message, $sessionData);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Bulk message sent',
            'sent' => $result['sent'],
            'failed' => $result['failed'],
            'details' => $result['details']
        ]);
    }

    /**
     * Send test message to specific contact
     */
    public function sendTestMessage()
    {
        $contactId = $this->request->getPost('contact_id');
        $message = $this->request->getPost('message');

        if (empty($contactId) || empty($message)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Contact ID and message are required'
            ]);
        }

        $contact = $this->contactModel->find($contactId);
        if (!$contact) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Contact not found'
            ]);
        }

        // Load WhatsApp service
        $whatsappService = new \App\Modules\ClassroomNotifications\Services\WhatsAppService();

        $phone = $contact['whatsapp_number'] ?: $contact['phone_number'];
        $result = $whatsappService->sendMessage($phone, $message);

        return $this->response->setJSON($result);
    }

    /**
     * Get available students
     */
    protected function getStudents(): array
    {
        $db = \Config\Database::connect();
        return $db->table('students')
                 ->select('student_id, firstname, lastname, admission_no')
                 ->where('deleted_at', null)
                 ->orderBy('firstname', 'ASC')
                 ->get()
                 ->getResultArray();
    }
}
