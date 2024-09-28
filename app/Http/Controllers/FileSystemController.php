<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FileSystemController extends Controller
{
    public function upload(Request $request)
    {

        try {
            $request->validate([
                "file" => 'required|file|max:2048',
                'folder' => 'required'
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $folder = $request->input('folder');

                $path = Storage::disk('public')->putFile($folder, $file);
                $publicUrl = Storage::url($path);


                return response()->json([
                    'error' => false,
                    'message' => 'File uploaded successfully.',
                    'path' => $publicUrl
                ]);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'No file found',
                ]);
            }
        } catch (ValidationException $th) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $th->errors()
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $th->getMessage()
            ]);
        }
    }

    public function delete(Request $request)
    {
        try {
            $validate = $request->validate([
                'path' => 'required|string',
            ]);

            $filePath = $request->$validate['path'];
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);

                return response()->json([
                    'error' => false,
                    'message' => 'File deleted successfully.'
                ]);
            }

            return response()->json([
                'error' => true,
                'message' => 'File not found.'
            ]);
        } catch (ValidationException $th) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $th->errors()
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $th->getMessage()
            ]);
        }
    }
}
