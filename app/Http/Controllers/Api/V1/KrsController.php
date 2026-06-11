<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\KrsItem;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use App\Services\IaeIntegrationService;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class KrsController extends Controller
{
    #[OA\Get(
        path: "/v1/courses",
        summary: "Display a listing of courses and their remaining quota",
        tags: ["Courses"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Successful operation",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Courses retrieved successfully"),
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "code", type: "string", example: "IF-101"),
                            new OA\Property(property: "name", type: "string", example: "Pemrograman Dasar"),
                            new OA\Property(property: "credits", type: "integer", example: 3),
                            new OA\Property(property: "quota", type: "integer", example: 30),
                            new OA\Property(property: "remaining_quota", type: "integer", example: 30),
                            new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-06-02T07:50:50Z"),
                            new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-06-02T07:50:50Z")
                        ],
                        type: "object"
                    )
                ),
                new OA\Property(
                    property: "meta",
                    properties: [
                        new OA\Property(property: "count", type: "integer", example: 5)
                    ],
                    type: "object"
                )
            ],
            type: "object"
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Unauthorized access. X-IAE-KEY header is missing or invalid."),
                new OA\Property(
                    property: "errors",
                    properties: [
                        new OA\Property(property: "auth", type: "array", items: new OA\Items(type: "string", example: "Invalid API Key."))
                    ],
                    type: "object"
                )
            ],
            type: "object"
        )
    )]
    public function courses()
    {
        $courses = Course::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Courses retrieved successfully',
            'data' => $courses,
            'meta' => [
                'count' => $courses->count()
            ]
        ], 200);
    }

    #[OA\Get(
        path: "/v1/krs/{student_id}",
        summary: "Display the KRS draft items of a specific student",
        tags: ["KRS"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Parameter(
        name: "student_id",
        in: "path",
        description: "NIM / ID of the student",
        required: true,
        schema: new OA\Schema(type: "string", example: "102022400068")
    )]
    #[OA\Response(
        response: 200,
        description: "Successful operation",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "KRS draft retrieved successfully"),
                new OA\Property(
                    property: "data",
                    properties: [
                        new OA\Property(
                            property: "student",
                            properties: [
                                new OA\Property(property: "id", type: "string", example: "102022400068"),
                                new OA\Property(property: "name", type: "string", example: "Galih Pratama")
                            ],
                            type: "object"
                        ),
                        new OA\Property(
                            property: "items",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(
                                        property: "course",
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "code", type: "string", example: "IF-101"),
                                            new OA\Property(property: "name", type: "string", example: "Pemrograman Dasar"),
                                            new OA\Property(property: "credits", type: "integer", example: 3)
                                        ],
                                        type: "object"
                                    ),
                                    new OA\Property(property: "status", type: "string", example: "submitted"),
                                    new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-06-02T07:51:00Z")
                                ],
                                type: "object"
                            )
                        )
                    ],
                    type: "object"
                ),
                new OA\Property(
                    property: "meta",
                    properties: [
                        new OA\Property(property: "total_courses", type: "integer", example: 1),
                        new OA\Property(property: "total_credits", type: "integer", example: 3)
                    ],
                    type: "object"
                )
            ],
            type: "object"
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Unauthorized access. X-IAE-KEY header is missing or invalid."),
                new OA\Property(
                    property: "errors",
                    properties: [
                        new OA\Property(property: "auth", type: "array", items: new OA\Items(type: "string", example: "Invalid API Key."))
                    ],
                    type: "object"
                )
            ],
            type: "object"
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Student not found",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Student not found"),
                new OA\Property(
                    property: "errors",
                    properties: [
                        new OA\Property(property: "student_id", type: "array", items: new OA\Items(type: "string", example: "Student with the given ID does not exist."))
                    ],
                    type: "object"
                )
            ],
            type: "object"
        )
    )]
    public function krs($student_id)
    {
        $student = Student::find($student_id);

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found',
                'errors' => [
                    'student_id' => ['Student with the given ID does not exist.']
                ]
            ], 404);
        }

        $krsItems = KrsItem::with('course')
            ->where('student_id', $student_id)
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'KRS draft retrieved successfully',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                ],
                'items' => $krsItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'course' => $item->course,
                        'status' => $item->status,
                        'created_at' => $item->created_at,
                    ];
                })
            ],
            'meta' => [
                'total_courses' => $krsItems->count(),
                'total_credits' => $krsItems->sum(fn($item) => $item->course->credits)
            ]
        ], 200);
    }

    #[OA\Post(
        path: "/v1/krs/submit",
        summary: "Submit a KRS transaction (register student to a course)",
        tags: ["KRS"],
        security: [["ApiKeyAuth" => [], "BearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["student_id", "course_id"],
            properties: [
                new OA\Property(property: "student_id", type: "string", example: "102022400068"),
                new OA\Property(property: "course_id", type: "integer", example: 1)
            ],
            type: "object"
        )
    )]
    #[OA\Response(
        response: 201,
        description: "KRS submitted successfully",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "KRS submitted successfully"),
                new OA\Property(
                    property: "data",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "student_id", type: "string", example: "102022400068"),
                        new OA\Property(
                            property: "course",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "code", type: "string", example: "IF-101"),
                                new OA\Property(property: "name", type: "string", example: "Pemrograman Dasar"),
                                new OA\Property(property: "credits", type: "integer", example: 3),
                                new OA\Property(property: "remaining_quota", type: "integer", example: 29)
                            ],
                            type: "object"
                        ),
                        new OA\Property(property: "status", type: "string", example: "submitted")
                    ],
                    type: "object"
                ),
                new OA\Property(
                    property: "meta",
                    properties: [
                        new OA\Property(property: "timestamp", type: "string", format: "date-time", example: "2026-06-02T07:51:00Z")
                    ],
                    type: "object"
                )
            ],
            type: "object"
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Bad Request (Quota full or course already taken)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Quota full"),
                new OA\Property(
                    property: "errors",
                    properties: [
                        new OA\Property(property: "course_id", type: "array", items: new OA\Items(type: "string", example: "The quota for this course is full."))
                    ],
                    type: "object"
                )
            ],
            type: "object"
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Unauthorized access. X-IAE-KEY header is missing or invalid."),
                new OA\Property(
                    property: "errors",
                    properties: [
                        new OA\Property(property: "auth", type: "array", items: new OA\Items(type: "string", example: "Invalid API Key."))
                    ],
                    type: "object"
                )
            ],
            type: "object"
        )
    )]
    #[OA\Response(
        response: 422,
        description: "Validation error",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "error"),
                new OA\Property(property: "message", type: "string", example: "Validation error"),
                new OA\Property(
                    property: "errors",
                    properties: [
                        new OA\Property(property: "student_id", type: "array", items: new OA\Items(type: "string", example: "The selected student id is invalid."))
                    ],
                    type: "object"
                )
            ],
            type: "object"
        )
    )]
    public function submit(Request $request, IaeIntegrationService $integration)
    {
        $request->validate([
            'student_id' => 'required|string',
            'course_id' => 'required|integer'
        ]);

        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Token JWT tidak ditemukan'], 401);
        }

        try {
            // Mulai transaksi database
            $krsItem = DB::transaction(function () use ($request, $integration, $token) {
                
                // 1. Ambil Course & kunci baris ini agar sisa_kuota aman dari race condition
                $course = Course::where('id', $request->course_id)->lockForUpdate()->firstOrFail();
                
                if ($course->remaining_quota < 1) {
                    throw new \Exception("Kuota penuh!");
                }

                // 2. Kurangi kuota & simpan transaksi
                $course->decrement('remaining_quota');
                
                $item = KrsItem::create([
                    'student_id' => $request->student_id,
                    'course_id' => $course->id,
                    'status' => 'submitted'
                ]);

                $transactionData = [
                    'student_id' => $item->student_id,
                    'course_id' => $item->course_id,
                    'status' => 'submitted'
                ];

                // 3. Panggil Legacy SOAP (Modul 2)
                // Jika ini gagal, Exception akan dilempar dan database otomatis di-rollback
                $integration->sendSoapAudit($token, $transactionData);
                
                // 4. Panggil AMQP Publisher untuk Service Kurikulum (Modul 3)
                $integration->publishEvent($token, $transactionData);

                return $item;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'KRS berhasil diajukan dan dicatat di sistem terpusat.',
                'data' => $krsItem
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
