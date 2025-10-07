<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Patient;
use App\Model\Appointment;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\DbConnection\Db;

#[AutoController]
class PatientController extends AbstractController {
    public function store(RequestInterface $request, ResponseInterface $response)
    {
        $data = $request->all();
        var_dump($data);

        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['gender']) || empty($data['birth_date'])) {
            return $response->json(['error' => 'All fields are required'], 422);
        }

        $patient = new Patient();
        $patient->first_name = $data['first_name'];
        $patient->last_name = $data['last_name'];
        $patient->birth_date = $data['birth_date'];
        $patient->gender = $data['gender'];
        
        $patient->save();

        return $response->json([
            'message' => 'Пациент создан успешно',
            'data' => $patient,
        ]);
    }

    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $query = Patient::query();

        $search = $request->input('search', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 10);

        /** @var LengthAwarePaginator $patients */
        $patients = $query->paginate($perPage, ['*'], 'page', $page);

        return $response->json($patients);
    }


    public function show(RequestInterface $request, ResponseInterface $response)
    {
        $id = (int) $request->input('id');
        
        $patient = Patient::query()->find($id);
        if (!$patient) {
            return $response->json(['message' => 'Пациент не найден'], 404);
        }

        return $response->json($patient);
    }

    public function update(RequestInterface $request, ResponseInterface $response)
    {
        $id = (int) $request->input('id');

        $patient = Patient::query()->find($id);
        if (!$patient) {
            return $response->json(['message' => 'Пациент не найден'], 404);
        }

        $data = $request->all();
        $patient->first_name = $data['first_name'] ?? $patient->first_name;
        $patient->last_name = $data['last_name'] ?? $patient->last_name;
        $patient->birth_date = $data['birth_date'] ?? $patient->birth_date;
        $patient->gender = $data['gender'] ?? $patient->gender;
        $patient->save();

        return $response->json(['message' => 'Данные пациента обновлены', 'data' => $patient]);
    }

    public function destroy(RequestInterface $request, ResponseInterface $response)
    {
        $id = (int) $request->input('id');
        
        $patient = Patient::query()->find($id);
        if (!$patient) {
            return $response->json(['message' => 'Пациент не найден'], 404);
        }

        Db::transaction(function () use ($patient) {
            Appointment::query()->where('patient_id', $patient->id)->delete();
            $patient->delete();
        });

        return $response->json(['message' => 'Пациент и его записи успешно удалены']);
    }
}