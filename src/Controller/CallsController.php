<?php

namespace App\Controller;

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
     * in the public/calls-data directory.
     * 
     * @param Request $request The HTTP request
     * @return JsonResponse Response with upload status
     */
    #[Route('/api/upload-calls', name: 'api_upload_calls', methods: ['POST'])]
    public function uploadCalls(Request $request): JsonResponse
    {
        // Get the uploaded file from the request
        $file = $request->files->get('callsFile');

        // Handle case where file is not found with the expected key
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

        // Ensure the upload directory exists
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/calls-data';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move the file to the upload directory
        try {
            $file->move($uploadDir, $newFilename);

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
