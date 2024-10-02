<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Classroom;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
         // Cari ID kelas berdasarkan nama kelas
        $classroom = Classroom::where('title', $row['classroom_name'])->first();

        return new Student([
            'nisn'          => (int) $row['nisn'],
            'name'          => $row['name'],
            'password'      => $row['password'],
            'gender'        => $row['gender'],
            'classroom_id'  => $classroom ? $classroom->id : null,
            // 'classroom_id'  => (int) $row['classroom_id'],
        ]);
    }

    public function rules(): array
    {
        return [
            'nisn' => 'unique:students,nisn',
        ];
    }
}
