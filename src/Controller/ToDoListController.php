<?php

namespace App\Controller;

use App\Entity\Todo;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\Type\TodoType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/todolist")
 * @IsGranted("ROLE_USER")
 */
class ToDoListController extends AbstractController {

  /**
   * @Route("/", name="todolist")
   */
  public function todoList(Request $request): Response {
    // get user
    $user = $this->getUser();
    // get back all todos from the data base
    // TODO: delete old row: $todos = $this->getDoctrine()->getRepository(Todo::class)->findAll();
    $todos = $user->getTodos();
    // create the 'add todo' form and handle the request
    $todoForm = $this->createForm(TodoType::class, new Todo());
    $todoForm->handleRequest($request);
    if ($todoForm->isSubmitted() && $todoForm->isValid()) {
      // get back the data from the form
      $todo = $todoForm->getData();
      $todo->setUserId($user);
      // store the todo in the data base
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($user);
      $entityManager->persist($todo);
      $entityManager->flush();
      // redirect to '/list' to avoid that the user refresh the page
      return $this->redirectToRoute('todolist');
    }
    // return the coresponding page
    return $this->render('ToDoList.html.twig', [
      'todos' => $todos,
      'todoForm' => $todoForm->createView()
    ]);
  }

  /**
   * @Route("/delete/{id}", name="delete")
   */
  public function delete($id): Response {
    // get antity manager
    $entityManager = $this->getDoctrine()->getManager();
    // find and store the todo to remove
    $todoToRemove = $entityManager->getRepository(Todo::class)->find($id);
    // remove it from the database
    $entityManager->remove($todoToRemove);
    $entityManager->flush();
    // redirect to /todolist
    return $this->redirectToRoute('todolist');
  }

  /**
   * @Route("/edit/{id}")
   */
  public function edit(Request $request, $id): Response {
    // get entity manager
    $entityManager = $this->getDoctrine()->getManager();
    // find and store the todo to edit
    $todoToEdit = $entityManager->getRepository(Todo::class)->find($id);
    // create the 'edit todo' form and handle the request
    $todoForm = $this->createForm(TodoType::class, new Todo());
    $todoForm->get('name')->setData($todoToEdit->getName());  //////////////////////////////////////////////////////////////////////////////////
    $todoForm->handleRequest($request);
    if ($todoForm->isSubmitted() && $todoForm->isValid()) {
      // get back the data from the form
      $todoFromForm = $todoForm->getData();
      // set the new todo name and actualize the data base
      $todoToEdit->setName($todoFromForm->getName());
      $entityManager->flush();
      // redirect to '/list' to avoid that the user refresh the page
      return $this->redirectToRoute('todolist');
    }
    // return the coresponding page
    return $this->render('EditTodo.html.twig', [
      'todo' => $todoToEdit,
      'todoForm' => $todoForm->createView()
    ]);
  }
}