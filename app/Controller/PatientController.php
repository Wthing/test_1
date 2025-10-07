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
use Hyperf\Swagger\Annotation as SA;
use Hyperf\Swagger\Request\SwaggerRequest;

#[AutoController]
#[SA\Server(url: 'http://localhost:950', description: 'Local API Server')]
#[SA\Tag(name: 'Patients', description: 'Управление пациентами')]
class PatientController extends AbstractController {

    #[SA\Post(summary: 'Создать пациента', tags: ['Patients'])]
    public function create(RequestInterface $request, ResponseInterface $response)
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

    #[SA\Get(summary: 'Найти пациентов по имени или фамилии', tags: ['Patients'])]
    #[SA\Parameter(name: 'search', in: 'query', required: false, schema: new SA\Schema(type: 'string'), description: 'Имя или фамилия пациента')]
    #[SA\Parameter(name: 'page', in: 'query', required: false, schema: new SA\Schema(type: 'integer'), description: 'Номер страницы')]
    #[SA\Parameter(name: 'per_page', in: 'query', required: false, schema: new SA\Schema(type: 'integer'), description: 'Количество элементов на странице')]
    #[SA\Response(response: 200, description: 'Результаты поиска', content: new SA\JsonContent(example: '{"data": [...]}'))]
    public function getByName(RequestInterface $request, ResponseInterface $response)
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

    #[SA\Get(summary: 'Получить данные пациента по ID', tags: ['Patients'])]
    #[SA\Parameter(name: 'id', in: 'query', required: true, schema: new SA\Schema(type: 'integer'), description: 'ID пациента')]
    #[SA\Response(response: 200, description: 'Информация о пациенте', content: new SA\JsonContent(example: '{"id": 1, "first_name": "Асылхан"}'))]
    #[SA\Response(response: 404, description: 'Пациент не найден', content: new SA\JsonContent(example: '{"message": "Пациент не найден"}'))]
    public function getById(RequestInterface $request, ResponseInterface $response)
    {
        $id = (int) $request->input('id');
        
        $patient = Patient::query()->find($id);
        if (!$patient) {
            return $response->json(['message' => 'Пациент не найден'], 404);
        }

        return $response->json($patient);
    }

    #[SA\Put(summary: 'Обновить данные пациента', tags: ['Patients'])]
    #[SA\RequestBody(
        description: 'Изменяемые данные пациента',
        content: new SA\JsonContent(
            required: ['id'],
            properties: [
                new SA\Property(property: 'id', type: 'integer', example: 1, description: 'ID пациента'),
                new SA\Property(property: 'first_name', type: 'string', example: 'Асылхан', description: 'Имя пациента'),
                new SA\Property(property: 'last_name', type: 'string', example: 'Болатов', description: 'Фамилия пациента'),
                new SA\Property(property: 'birth_date', type: 'string', example: '2001-03-14', description: 'Дата рождения'),
                new SA\Property(property: 'gender', type: 'string', example: 'Мужской', description: 'Пол пациента'),
            ]
        )
    )]
    #[SA\Response(response: 200, description: 'Данные обновлены', content: new SA\JsonContent(example: '{"message": "Данные пациента обновлены"}'))]
    #[SA\Response(response: 404, description: 'Пациент не найден', content: new SA\JsonContent(example: '{"message": "Пациент не найден"}'))]
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

    #[SA\Delete(summary: 'Удалить пациента и все его записи', tags: ['Patients'])]
    #[SA\Parameter(name: 'id', in: 'query', required: true, schema: new SA\Schema(type: 'integer'), description: 'ID пациента')]
    #[SA\Response(response: 200, description: 'Удалено успешно', content: new SA\JsonContent(example: '{"message": "Пациент и его записи успешно удалены"}'))]
    #[SA\Response(response: 404, description: 'Пациент не найден', content: new SA\JsonContent(example: '{"message": "Пациент не найден"}'))]
    public function delete(RequestInterface $request, ResponseInterface $response)
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