<?php

namespace App\Controller;

use App\Entity\UploadedFile;
use App\Repository\UploadedFileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for handling calls-related operations
 */
class CallsController extends AbstractController
{
    /**
     * Handles file upload for calls data
     * 
     * This endpoint accepts CSV files and saves them with a unique filename
     * in the configured upload directory. It also creates a record in the
     * uploaded_files table to track the file for further processing.
     * 
     * @param Request $request The HTTP request
     * @param UploadedFileRepository $uploadedFileRepository Repository for UploadedFile entities
     * @return JsonResponse Response with upload status
     */
    #[Route('/api/upload-calls', name: 'api_upload_calls', methods: ['POST'])]
    public function uploadCalls(Request $request, UploadedFileRepository $uploadedFileRepository): JsonResponse
    {
        // Get the uploaded file from the request
        $file = $request->files->get('callsFile');

        // Handle case where the file is not found with the expected key
        if (!$file) {
            if ($request->files->count() > 0) {
                $files = $request->files->all();
                if (count($files) > 0) {
                    $firstKey = array_key_first($files);
                    $file = $files[$firstKey];
                }
            } else {
                return new JsonResponse([
                    'success' => false, 
                    'message' => 'No file uploaded'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        // Validate file type
        if ($file->getClientOriginalExtension() !== 'csv') {
            return new JsonResponse([
                'success' => false, 
                'message' => 'Only CSV files are allowed'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Generate a unique filename
        $randomChars = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
        $timestamp = time();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $originalName . $randomChars . '_' . $timestamp . '.csv';

        // Get upload path from parameter
        $uploadPath = $this->getParameter('kernel.project_dir') . '/' . $this->getParameter('upload_path');
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Move the file to the upload directory
        try {
            $file->move($uploadPath, $newFilename);

            // Create a new UploadedFile entity
            $uploadedFile = new UploadedFile();
            $uploadedFile->setFileName($newFilename);
            $uploadedFile->setUploadedAt(new \DateTime());
            $uploadedFile->setStatus('pending');

            // Save the entity to the database
            $uploadedFileRepository->save($uploadedFile, true);

            return new JsonResponse([
                'success' => true, 
                'message' => 'File uploaded successfully',
                'filename' => $newFilename
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false, 
                'message' => 'Failed to upload file: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
