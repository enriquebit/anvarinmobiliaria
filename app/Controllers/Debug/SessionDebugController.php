<?php

namespace App\Controllers\Debug;

use CodeIgniter\Controller;

class SessionDebugController extends Controller
{
    public function clearSession()
    {
        // Force logout
        if (auth()->loggedIn()) {
            auth()->logout();
        }
        
        // Clear session
        session()->destroy();
        
        // Clear all session data
        $session = session();
        $session->remove('user');
        $session->remove('auth_login_info');
        $session->remove('auth_user_id');
        $session->remove('auth_groups');
        $session->remove('auth_permissions');
        
        // Clear cookies
        helper('cookie');
        delete_cookie('ci_session');
        delete_cookie('remember');
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Session cleared successfully'
        ]);
    }
    
    public function checkSession()
    {
        $data = [
            'logged_in' => auth()->loggedIn(),
            'user_id' => auth()->id(),
            'session_data' => session()->get(),
            'cookies' => $_COOKIE
        ];
        
        return $this->response->setJSON($data);
    }
}