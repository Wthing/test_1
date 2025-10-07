<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Appointment;
use App\Model\Patient;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\DbConnection\Db;

#[AutoController]
class AppointmentController extends AbstractController {
    public function create(RequestInterface $request, ResponseInterface $response)
    {
        $data = $request->all();

        $patient = Patient::query()->find($data['patient_id'] ?? 0);
        if (!$patient) {
            return $response->json(['message' => 'Пациент не найден'], 404);
        }

        if (empty($data['date_time']) || strtotime($data['date_time']) < time()) {
            return $response->json(['message' => 'Некорректное или прошедшее время приёма'], 422);
        }

        $dateTime = $data['date_time'];

        $existsPatientConflict = Appointment::query()
            ->where('patient_id', $data['patient_id'])
            ->where('date_time', $dateTime)
            ->exists();

        if ($existsPatientConflict) {
            return $response->json(['message' => 'У пациента уже есть запись на это время'], 422);
        }

        $existsDoctorConflict = Appointment::query()
            ->where('doctor_name', $data['doctor_name'])
            ->where('date_time', $dateTime)
            ->exists();

        if ($existsDoctorConflict) {
            return $response->json(['message' => 'Врач уже принимает пациента в это время'], 422);
        }

        $appointment = new Appointment();
        $appointment->patient_id = $data['patient_id'];
        $appointment->doctor_name = $data['doctor_name'] ?? '';
        $appointment->specialization = $data['specialization'] ?? '';
        $appointment->date_time = $data['date_time'];
        $appointment->save();

        return $response->json(['message' => 'Запись успешно создана', 'data' => $appointment]);
    }

    public function getByDoctorNameOrSpecialization(RequestInterface $request, ResponseInterface $response)
    {
        $query = Appointment::query()->with('patient');

        // Фильтры
        if ($doctor = $request->input('doctor_name')) {
            $query->where('doctor_name', 'like', "%{$doctor}%");
        }
        if ($spec = $request->input('specialization')) {
            $query->where('specialization', 'like', "%{$spec}%");
        }

        // Сортировка по дате
        $sortOrder = strtolower($request->input('sort', 'asc')) === 'desc' ? 'desc' : 'asc';
        $query->orderBy('date_time', $sortOrder);

        // Пагинация
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 10);

        $appointments = $query->paginate($perPage, ['*'], 'page', $page);

        return $response->json($appointments);
    }

    public function getByPatientId(RequestInterface $request, ResponseInterface $response)
    {
        $patient_id = (int) $request->input('patient_id');

        $appointments = Appointment::query()
            ->where('patient_id', $patient_id)
            ->orderBy('date_time', 'asc')
            ->get();

        if ($appointments->isEmpty()) {
            return $response->json(['message' => 'У пациента нет записей']);
        }

        return $response->json(['data' => $appointments]);
    }

    public function delete(RequestInterface $request    , ResponseInterface $response)
    {
        $id = (int) $request->input('id');

        $appointment = Appointment::query()->find($id);
        if (!$appointment) {
            return $response->json(['message' => 'Запись не найдена'], 404);
        }

        $appointment->delete();

        return $response->json(['message' => 'Запись отменена', 'data' => $appointment]);
    }
}