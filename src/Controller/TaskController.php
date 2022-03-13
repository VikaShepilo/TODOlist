<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{   

    #[Route('/', name: 'task')]
    public function ajaxAction(Request $request)
    {
        $task = new Task();
        $form = $this->createForm(TaskFormType::class, $task);
        $form->handleRequest($request);

        $tasks = $this->getDoctrine() 
            ->getRepository(Task::class) 
            ->findAll(); 
        if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {  
            $jsonData = array();  
            $idx = 0;  
            foreach($tasks as $task) {  
                $temp = array(
                    'idTask' => $task->getId(),
                    'description' => $task->getDescription(),  
                    'date' => $task->getDate(),  
                    'completed' => $task->getCompleted(),
                );   
                $jsonData[$idx++] = $temp;  
            } 
            return new JsonResponse($jsonData); 
        } else { 
            return $this->render('task/ajax.html.twig', [
                'form' => $form->createView()
            ]); 
        }  
    }

    #[Route('/create', name: 'taskcreate')]
    public function ajaxCreateAction(Request $request)
    {
        $task = new Task();
        $form = $this->createForm(TaskFormType::class, $task);
        $form->handleRequest($request);

        $data = $request->request->all();
        $description =$data['task_form']['description'];
        $date =$data['task_form']['date'];
        if ($request->isXmlHttpRequest()) {

            if ($form->isSubmitted() && $form->isValid()) {

                $task->setDescription($description);
                $task->setDate(new \DateTime($date));
                $task->setCompleted(0);
        
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($task);
                $entityManager->flush();
        
                return new JsonResponse($data);
            }

        }
        return $this->render('task/ajax.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/delete', name: 'delete')]
    public function ajaxDeleteAction(Request $request)
    {
        $data = $request->request->all();
        $id = $data['id'];
        if ($request->isXmlHttpRequest()) {  
            $em = $this->getDoctrine()->getManager();
            $taskDelete = $em->getRepository(Task::class)->find($id);
            $em->remove($taskDelete);
            $em->flush();
            return new JsonResponse($data);

        }
        return $this->render('task/ajax.html.twig');
    }

    #[Route('/update', name: 'update')]
    public function ajaxUpdateAction(Request $request)
    {
        $data = $request->request->all();
        $id = $data['id'];
        $completed = $data['completed'];
        if ($completed == 'true') {
            $complet = false;
        }else {
            $complet = true;
        }
        if ($request->isXmlHttpRequest()) {  
            $em = $this->getDoctrine()->getManager();
            $task = $em->getRepository(Task::class)->find($id);
            $task->setCompleted($complet);
            $em->flush();

            return new JsonResponse($data);
        }
        return $this->render('task/ajax.html.twig');
    }

    #[Route('/change', name: 'change')]
    public function ajaxChangeTask(Request $request)
    {
        $data = $request->request->all();
        $id = $data['id'];

        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository(Task::class)->find($id);

        $description = $task->getDescription();
        $date = $task->getDate();
        $completed = $task->getCompleted();
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([$id, $description, $date, $completed]);
        }
        return $this->render('task/ajax.html.twig');
    }

    #[Route('/change/task', name: 'changetask')]
    public function ajaxChangeYouTask(Request $request)
    {
        $data = $request->request->all();
        $data = $data['data'];
        $id= $data['id'];
        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository(Task::class)->find($id);

        if ($request->isXmlHttpRequest()) {
            $task->setDescription($data['description']);
            $task->setDate(new \DateTime($data['date']));
            $em->flush();
            return new JsonResponse($data);
        }
        return $this->render('task/ajax.html.twig');
    }
}
