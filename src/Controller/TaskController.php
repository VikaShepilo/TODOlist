<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{   
    #[Route('/', name: 'task_list')]
    public function listTask()
    {
        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository(Task::class)->findBy([], []);

        return $this->render('task_list/index.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/create', name: 'app_task')]
    public function createTask(Request $request)
    {

        $task = new Task();
        $form = $this->createForm(TaskFormType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setCompleted(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($task );
            $em->flush();

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete', name: 'delete_task')]
    public function deleteTask(Request $request)
    {
        $id = $request->query->get('id');

        $em = $this->getDoctrine()->getManager();
        $taskDelete = $em->getRepository(Task::class)->find($id);
        $em->remove($taskDelete);
        $em->flush();

        return $this->redirectToRoute('task_list');
    }

    #[Route('/update', name: 'update_completed')]
    public function updateTask(Request $request)
    {
        $id = $request->query->get('id');
        $completed = $request->query->get('completed');

        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository(Task::class)->find($id);

        $task->setCompleted(!$completed);

        $em->flush();

        return $this->redirectToRoute('task_list');
    }

    #[Route('/change', name: 'change_task')]
    public function changeTask(Request $request)
    {
        $id = $request->query->get('id');

        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository(Task::class)->find($id);

        $form = $this->createForm(TaskFormType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $task->setId($id);

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('task_list');
        }

        return $this->render('change_task/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
