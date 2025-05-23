<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AdminModel;
use App\Models\MembersModel;
use App\Models\BooksModel;


class Form extends BaseController
{
    // To open the form
    protected $helpers = ['form'];


    // Admin Register Function

    public function register()
    {   
        $adminModel = new AdminModel();

        // Load the validation service
        $validation = \Config\Services::validation();

        if (!$this->request->is('post')) {
            return redirect()->to('/register');
        }

        $rules = [
            'username' => 'required',
            'email' => 'required|valid_email',
            'password' => 'required|min_length[5]|max_length[20]',
            'ConfirmPassword' => 'required|min_length[5]|max_length[20]|matches[password]'
        ];


        $data = $this->request->getPost(array_keys($rules));

        if (!$this->validateData($data, $rules)) {
            return redirect()->to('/register')->with('errors', $validation->getErrors());
        }
        else 
        {
            $data = array(
                'username' => esc($this->request->getPost('username')),
                'email' => esc($this->request->getPost('email')),
                'pwd' => esc($this->request->getPost('password')),
            );
    
            $adminModel->insert($data);
            return redirect()->to('register')->with('confirm', 'Registration Successful!');
        }
        
    }


    // Admin Login Function

    public function login() {
        $validation = \Config\Services::validation();


        if (!$this->request->is('post')) {
            return redirect()->to('/register');
        }

        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        $data = $this->request->getPost(array_keys($rules));

        // Validate the data
        if (!$this->validateData($data, $rules)) {
            return redirect()->to('/')->with('errors', $validation->getErrors());
        }
        else 
        {
            $userModel = new AdminModel();

            $data = array(
                'username' => esc($this->request->getPost('username')),
                'pwd' => esc($this->request->getPost('password')),
            );

            
            $result = $userModel->where('username', $data['username'])->first();

            // Check if the user exists in the database
            if(!$result) {

                return redirect()->to('/')->with('errors', array('error' => 'Invalid Password or Username'));
            }
            else {

                if($data['pwd'] == $result['pwd']) {

                    // Set & Create session data that will access in Home & About page
                    $session = session();
                    $session->set('name', $data['username']);
                    
                    return redirect()->to('/home');
                }
                else {
                    return redirect()->to('/')->with('errors', array('error' => 'Invalid Password or Username'));
                }
            }
            
        }
    }

    // Add Members Function

    public function addMembers() {

        if(!session()->get('name')) {
            return redirect()->to('/');
        }

        $membersModel = new MembersModel();

        $one_month_later = date('Y-m-d', strtotime('+1 month'));

        $data = array(
            'firstname' => $this->request->getPost('firstname'),
            'lastname' => $this->request->getPost('lastname'),
            'address' => $this->request->getPost('address'),
            'membership_expiry_date' => $one_month_later,
            'phone_number' => $this->request->getPost('phoneNumber'),
        );

        $membersModel->insert($data);

        return redirect()->to('/members')->with('confirm', 'Add Member Sucessfully!');
    }

    // Update Members Function

    public function updateMembers($id) {

        if(!session()->get('name')) {
            return redirect()->to('/');
        }

        $membersModel = new MembersModel();

        $data = array(
            'firstname' => $this->request->getPost('firstname'),
            'lastname' => $this->request->getPost('lastname'),
            'address' => $this->request->getPost('address'),
            'phone_number' => $this->request->getPost('phoneNumber'),
        );

        $membersModel->where('member_id', $id)->set($data)->update();


        return redirect()->to('/members')->with('confirm', 'Update Member Sucessfully!');
    }


    // Add Books Function

    public function addBooks() {

        if(!session()->get('name')) {
            return redirect()->to('/');
        }

        $booksModel = new BooksModel();

        if($img = $this->request->getFile('bookImage')) {

            if($img->isValid() && ! $img->hasMoved()) {
                $imageName = $img->getRandomName();
                $img->move('images/', $imageName);
            }
        }

        $data = array(
            'title' => $this->request->getPost('title'),
            'author' => $this->request->getPost('author'),
            'genre' => $this->request->getPost('genre'),
            'book_img' => $imageName,
            'availability' => $this->request->getPost('availability'),
        );

        $booksModel->insert($data);

        return redirect()->to('/books')->with('confirm', 'Add Book Sucessfully!');
    }


    public function updateBook($id) {

        if(!session()->get('name')) {
            return redirect()->to('/');
        }

        $booksModel = new BooksModel();

        $db = \Config\Database::connect();

        // Check if the image is uploaded
        if($img = $this->request->getFile('bookImage')) {
            if($img->isValid() && ! $img->hasMoved()) {
                $imageName = $img->getRandomName();
                $img->move('images/', $imageName);
            }
        }

        // Check if the image name is set
        if(!empty($_FILES['bookImage']['name'])) {
            $db->query("UPDATE books SET book_img = '$imageName' WHERE book_id = $id");
        }

        $data = array(
            'title' => $this->request->getPost('title'),
            'author' => $this->request->getPost('author'),
            'genre' => $this->request->getPost('genre'),
            'availability' => $this->request->getPost('availability'),
        );

        $booksModel->where('book_id', $id)->set($data)->update();

        return redirect()->to('/books')->with('confirm', 'Update Book Sucessfully!');
    }
}

