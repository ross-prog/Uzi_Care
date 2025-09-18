<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PatientConsultationRecord;
use App\Models\NurseNote;
use Carbon\Carbon;

class PatientConsultationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleRecords = [
            [
                // Basic Information
                'student_employee_id' => '2024001234',
                'consultation_date_time' => Carbon::now()->subDays(5)->setTime(10, 30),
                
                // Personal Information
                'full_name' => 'Cruz, Maria Santos',
                'last_name' => 'Cruz',
                'first_name' => 'Maria',
                'middle_name' => 'Santos',
                'age' => 20,
                'birthdate' => Carbon::now()->subYears(20)->subMonths(3),
                'civil_status' => 'Single',
                'sex' => 'Female',
                'address' => '123 Main St, Barangay San Antonio, Manila City',
                'department' => 'School of Computing and Information Technology',
                'department_course' => 'Bachelor of Science in Computer Science',
                'contact_no' => '09123456789',
                
                // Guardian Information
                'guardian_name' => 'Rosa Cruz',
                'guardian_relationship' => 'Mother',
                'guardian_contact_no' => '09123456788',
                
                // Chief Complaints
                'chief_complaints' => 'Persistent headache for 3 days, mild fever, and fatigue',
                
                // Medical History
                'has_allergy' => true,
                'allergy_specify' => 'Penicillin, shellfish',
                'has_hypertension' => false,
                'has_diabetes' => false,
                'has_asthma' => false,
                'asthma_last_attack' => null,
                'other_medical_history' => 'No previous surgeries or hospitalizations',
                
                // Vital Signs (Time 1)
                'vital_signs_time_1' => '10:30',
                'weight' => 52.5,
                'height' => 158.0,
                'bmi' => 21.0,
                'last_menstrual_period' => Carbon::now()->subDays(12),
                'blood_pressure_1' => '110/70',
                'heart_rate_1' => '78',
                'respiratory_rate_1' => '18',
                'temperature_1' => 37.2,
                'oxygen_saturation_1' => '98',
                
                // Vital Signs (Time 2)
                'vital_signs_time_2' => '14:30',
                'blood_pressure_2' => '108/68',
                'heart_rate_2' => '76',
                'respiratory_rate_2' => '16',
                'temperature_2' => 36.8,
                'oxygen_saturation_2' => '99',
                
                // Diagnosis and Staff
                'diagnosis' => 'Viral syndrome, tension headache',
                'nurse_on_duty' => 'Nurse Jane Doe, RN',
                'physician_on_duty' => 'Dr. John Smith, MD',
                
                // Medicines and Equipment
                'medicines' => [
                    ['name' => 'Paracetamol', 'dosage' => '500mg', 'frequency' => '3x daily', 'duration' => '3 days'],
                    ['name' => 'Ibuprofen', 'dosage' => '400mg', 'frequency' => 'As needed for headache', 'duration' => '5 days']
                ],
                'equipment' => [
                    ['name' => 'Digital thermometer', 'purpose' => 'Temperature monitoring'],
                    ['name' => 'Blood pressure cuff', 'purpose' => 'BP monitoring']
                ]
            ],
            [
                // Basic Information
                'student_employee_id' => '2023001567',
                'consultation_date_time' => Carbon::now()->subDays(3)->setTime(14, 15),
                
                // Personal Information
                'full_name' => 'Garcia, Juan Dela Cruz',
                'last_name' => 'Garcia',
                'first_name' => 'Juan',
                'middle_name' => 'Dela Cruz',
                'age' => 22,
                'birthdate' => Carbon::now()->subYears(22)->subMonths(7),
                'civil_status' => 'Single',
                'sex' => 'Male',
                'address' => '456 Oak Avenue, Barangay Bagumbayan, Quezon City',
                'department' => 'School of Technician Studies',
                'department_course' => 'Bachelor of Science in Mechanical Engineering',
                'contact_no' => '09987654321',
                
                // Guardian Information
                'guardian_name' => 'Roberto Garcia',
                'guardian_relationship' => 'Father',
                'guardian_contact_no' => '09987654320',
                
                // Chief Complaints
                'chief_complaints' => 'Severe abdominal pain, nausea, loss of appetite for 2 days',
                
                // Medical History
                'has_allergy' => false,
                'allergy_specify' => null,
                'has_hypertension' => false,
                'has_diabetes' => false,
                'has_asthma' => true,
                'asthma_last_attack' => Carbon::now()->subMonths(6),
                'other_medical_history' => 'Childhood asthma, well-controlled with inhaler',
                
                // Vital Signs (Time 1)
                'vital_signs_time_1' => '14:15',
                'weight' => 68.0,
                'height' => 175.0,
                'bmi' => 22.2,
                'last_menstrual_period' => null,
                'blood_pressure_1' => '125/80',
                'heart_rate_1' => '88',
                'respiratory_rate_1' => '20',
                'temperature_1' => 37.8,
                'oxygen_saturation_1' => '97',
                
                // Vital Signs (Time 2)
                'vital_signs_time_2' => '16:00',
                'blood_pressure_2' => '120/78',
                'heart_rate_2' => '82',
                'respiratory_rate_2' => '18',
                'temperature_2' => 37.3,
                'oxygen_saturation_2' => '98',
                
                // Diagnosis and Staff
                'diagnosis' => 'Acute gastritis, possible food poisoning',
                'nurse_on_duty' => 'Nurse Mary Johnson, RN',
                'physician_on_duty' => 'Dr. Sarah Wilson, MD',
                
                // Medicines and Equipment
                'medicines' => [
                    ['name' => 'Omeprazole', 'dosage' => '20mg', 'frequency' => '2x daily before meals', 'duration' => '7 days'],
                    ['name' => 'Loperamide', 'dosage' => '2mg', 'frequency' => 'As needed for diarrhea', 'duration' => '3 days'],
                    ['name' => 'ORS', 'dosage' => '1 sachet', 'frequency' => 'Every 4 hours', 'duration' => '2 days']
                ],
                'equipment' => [
                    ['name' => 'IV cannula', 'purpose' => 'Fluid replacement'],
                    ['name' => 'Pulse oximeter', 'purpose' => 'O2 saturation monitoring']
                ]
            ],
            [
                // Basic Information
                'student_employee_id' => '2024002890',
                'consultation_date_time' => Carbon::now()->subDays(1)->setTime(9, 45),
                
                // Personal Information
                'full_name' => 'Rodriguez, Anna Luna',
                'last_name' => 'Rodriguez',
                'first_name' => 'Anna',
                'middle_name' => 'Luna',
                'age' => 19,
                'birthdate' => Carbon::now()->subYears(19)->subMonths(2),
                'civil_status' => 'Single',
                'sex' => 'Female',
                'address' => '789 Pine Street, Barangay Poblacion, Makati City',
                'department' => 'School of Entrepreneurship and Business Sciences',
                'department_course' => 'Bachelor of Science in Business Administration',
                'contact_no' => '09111222333',
                
                // Guardian Information
                'guardian_name' => 'Carmen Rodriguez',
                'guardian_relationship' => 'Mother',
                'guardian_contact_no' => '09111222332',
                
                // Chief Complaints
                'chief_complaints' => 'Sudden onset of skin rash, itching, and swelling after eating seafood',
                
                // Medical History
                'has_allergy' => true,
                'allergy_specify' => 'Seafood (shrimp, crab), latex',
                'has_hypertension' => false,
                'has_diabetes' => false,
                'has_asthma' => false,
                'asthma_last_attack' => null,
                'other_medical_history' => 'History of allergic reactions to seafood since childhood',
                
                // Vital Signs (Time 1)
                'vital_signs_time_1' => '09:45',
                'weight' => 48.5,
                'height' => 160.0,
                'bmi' => 18.9,
                'last_menstrual_period' => Carbon::now()->subDays(20),
                'blood_pressure_1' => '105/65',
                'heart_rate_1' => '92',
                'respiratory_rate_1' => '22',
                'temperature_1' => 36.7,
                'oxygen_saturation_1' => '99',
                
                // Vital Signs (Time 2)
                'vital_signs_time_2' => '11:30',
                'blood_pressure_2' => '108/68',
                'heart_rate_2' => '85',
                'respiratory_rate_2' => '18',
                'temperature_2' => 36.6,
                'oxygen_saturation_2' => '99',
                
                // Diagnosis and Staff
                'diagnosis' => 'Acute allergic reaction (urticaria), seafood allergy',
                'nurse_on_duty' => 'Nurse Bob Brown, RN',
                'physician_on_duty' => 'Dr. Emily Davis, MD',
                
                // Medicines and Equipment
                'medicines' => [
                    ['name' => 'Diphenhydramine', 'dosage' => '25mg', 'frequency' => '3x daily', 'duration' => '5 days'],
                    ['name' => 'Prednisolone', 'dosage' => '10mg', 'frequency' => '2x daily', 'duration' => '3 days'],
                    ['name' => 'Cetirizine', 'dosage' => '10mg', 'frequency' => '1x daily at bedtime', 'duration' => '7 days']
                ],
                'equipment' => [
                    ['name' => 'EpiPen', 'purpose' => 'Emergency epinephrine auto-injector'],
                    ['name' => 'Nebulizer', 'purpose' => 'Bronchodilator administration if needed']
                ]
            ],
            [
                // Basic Information
                'student_employee_id' => '9876543210',
                'consultation_date_time' => Carbon::today()->setTime(13, 20),
                
                // Personal Information
                'full_name' => 'Santos, Patricia Cruz',
                'last_name' => 'Santos',
                'first_name' => 'Patricia',
                'middle_name' => 'Cruz',
                'age' => 35,
                'birthdate' => Carbon::now()->subYears(35)->subMonths(8),
                'civil_status' => 'Married',
                'sex' => 'Female',
                'address' => '555 Cedar Avenue, Barangay Rosario, Pasig City',
                'department' => 'School of Human Resource Development',
                'department_course' => 'Faculty - College of Nursing',
                'contact_no' => '09777888999',
                
                // Guardian Information
                'guardian_name' => 'Miguel Santos',
                'guardian_relationship' => 'Spouse',
                'guardian_contact_no' => '09777888998',
                
                // Chief Complaints
                'chief_complaints' => 'Annual health check-up, routine wellness examination',
                
                // Medical History
                'has_allergy' => false,
                'allergy_specify' => null,
                'has_hypertension' => true,
                'has_diabetes' => false,
                'has_asthma' => false,
                'asthma_last_attack' => null,
                'other_medical_history' => 'Hypertension diagnosed 2 years ago, well-controlled with medication. No other significant medical history.',
                
                // Vital Signs (Time 1)
                'vital_signs_time_1' => '13:20',
                'weight' => 58.0,
                'height' => 162.0,
                'bmi' => 22.1,
                'last_menstrual_period' => Carbon::now()->subDays(8),
                'blood_pressure_1' => '130/85',
                'heart_rate_1' => '72',
                'respiratory_rate_1' => '16',
                'temperature_1' => 36.5,
                'oxygen_saturation_1' => '99',
                
                // Vital Signs (Time 2)
                'vital_signs_time_2' => '15:00',
                'blood_pressure_2' => '128/82',
                'heart_rate_2' => '70',
                'respiratory_rate_2' => '16',
                'temperature_2' => 36.4,
                'oxygen_saturation_2' => '99',
                
                // Diagnosis and Staff
                'diagnosis' => 'Routine health maintenance, controlled hypertension',
                'nurse_on_duty' => 'Nurse Karen White, RN',
                'physician_on_duty' => 'Dr. Robert Taylor, MD',
                
                // Medicines and Equipment
                'medicines' => [
                    ['name' => 'Amlodipine', 'dosage' => '5mg', 'frequency' => '1x daily in the morning', 'duration' => 'Continue current regimen'],
                    ['name' => 'Multivitamins', 'dosage' => '1 tablet', 'frequency' => '1x daily', 'duration' => 'Daily maintenance']
                ],
                'equipment' => [
                    ['name' => 'Automated BP monitor', 'purpose' => 'Home blood pressure monitoring'],
                    ['name' => 'Weight scale', 'purpose' => 'Weight monitoring']
                ]
            ],
            [
                // Basic Information
                'student_employee_id' => '2024003456',
                'consultation_date_time' => Carbon::now()->subDays(7)->setTime(11, 00),
                
                // Personal Information
                'full_name' => 'Reyes, Mark Anthony Villanueva',
                'last_name' => 'Reyes',
                'first_name' => 'Mark Anthony',
                'middle_name' => 'Villanueva',
                'age' => 21,
                'birthdate' => Carbon::now()->subYears(21)->subMonths(5),
                'civil_status' => 'Single',
                'sex' => 'Male',
                'address' => '123 Hospitality Drive, Barangay Tourism, Baguio City',
                'department' => 'School of Hospitality Management',
                'department_course' => 'Bachelor of Science in Hotel and Restaurant Management',
                'contact_no' => '09191234567',
                
                // Guardian Information
                'guardian_name' => 'Elena Reyes',
                'guardian_relationship' => 'Mother',
                'guardian_contact_no' => '09191234566',
                
                // Chief Complaints
                'chief_complaints' => 'Minor cut on hand from kitchen accident during culinary class',
                
                // Medical History
                'has_allergy' => false,
                'allergy_specify' => null,
                'has_hypertension' => false,
                'has_diabetes' => false,
                'has_asthma' => false,
                'asthma_last_attack' => null,
                'other_medical_history' => null,
                
                // Vital Signs (Time 1)
                'vital_signs_time_1' => '11:00',
                'blood_pressure_1' => '118/75',
                'heart_rate_1' => '78',
                'respiratory_rate_1' => '18',
                'temperature_1' => 36.5,
                'oxygen_saturation_1' => '99',
                
                // Vital Signs (Time 2)
                'vital_signs_time_2' => '11:30',
                'blood_pressure_2' => '120/76',
                'heart_rate_2' => '76',
                'respiratory_rate_2' => '17',
                'temperature_2' => 36.6,
                'oxygen_saturation_2' => '99',
                
                // Diagnosis and Staff
                'diagnosis' => 'Minor laceration on left hand, cleaned and dressed',
                'nurse_on_duty' => 'Nurse Maria Santos, RN',
                'physician_on_duty' => 'Dr. Jose Martinez, MD',
                
                // Medicines and Equipment
                'medicines' => [
                    ['name' => 'Betadine solution', 'dosage' => 'Topical', 'frequency' => '2x daily', 'duration' => '5 days'],
                    ['name' => 'Paracetamol', 'dosage' => '500mg', 'frequency' => 'As needed for pain', 'duration' => '3 days']
                ],
                'equipment' => [
                    ['name' => 'Sterile gauze', 'purpose' => 'Wound dressing'],
                    ['name' => 'Medical tape', 'purpose' => 'Securing dressing']
                ]
            ],
            [
                // Basic Information
                'student_employee_id' => '2024004567',
                'consultation_date_time' => Carbon::now()->subDays(2)->setTime(15, 30),
                
                // Personal Information
                'full_name' => 'Mendoza, Sarah Jane Flores',
                'last_name' => 'Mendoza',
                'first_name' => 'Sarah Jane',
                'middle_name' => 'Flores',
                'age' => 20,
                'birthdate' => Carbon::now()->subYears(20)->subMonths(9),
                'civil_status' => 'Single',
                'sex' => 'Female',
                'address' => '789 Creative Avenue, Barangay Arts, Makati City',
                'department' => 'School of Creative Arts, Media and Social Sciences',
                'department_course' => 'Bachelor of Fine Arts in Digital Arts',
                'contact_no' => '09123456789',
                
                // Guardian Information
                'guardian_name' => 'Carlos Mendoza',
                'guardian_relationship' => 'Father',
                'guardian_contact_no' => '09123456788',
                
                // Chief Complaints
                'chief_complaints' => 'Eye strain and headaches from prolonged computer work',
                
                // Medical History
                'has_allergy' => false,
                'allergy_specify' => null,
                'has_hypertension' => false,
                'has_diabetes' => false,
                'has_asthma' => false,
                'asthma_last_attack' => null,
                'other_medical_history' => 'Myopia (-2.5 both eyes)',
                
                // Vital Signs (Time 1)
                'vital_signs_time_1' => '15:30',
                'blood_pressure_1' => '110/70',
                'heart_rate_1' => '72',
                'respiratory_rate_1' => '16',
                'temperature_1' => 36.7,
                'oxygen_saturation_1' => '99',
                
                // Vital Signs (Time 2)
                'vital_signs_time_2' => '16:00',
                'blood_pressure_2' => '112/72',
                'heart_rate_2' => '74',
                'respiratory_rate_2' => '16',
                'temperature_2' => 36.6,
                'oxygen_saturation_2' => '99',
                
                // Diagnosis and Staff
                'diagnosis' => 'Computer vision syndrome, digital eye strain',
                'nurse_on_duty' => 'Nurse Jennifer Cruz, RN',
                'physician_on_duty' => 'Dr. Lisa Garcia, MD',
                
                // Medicines and Equipment
                'medicines' => [
                    ['name' => 'Artificial tears', 'dosage' => '1-2 drops', 'frequency' => '4x daily', 'duration' => '2 weeks'],
                    ['name' => 'Ibuprofen', 'dosage' => '200mg', 'frequency' => 'As needed for headache', 'duration' => '1 week']
                ],
                'equipment' => [
                    ['name' => 'Eye chart', 'purpose' => 'Visual acuity testing'],
                    ['name' => 'Ophthalmoscope', 'purpose' => 'Eye examination']
                ]
            ]
        ];

        foreach ($sampleRecords as $record) {
            $consultation = PatientConsultationRecord::create($record);
            
            // Add some nurse notes for a few records
            if (in_array($consultation->id, [1, 3, 4])) {
                NurseNote::create([
                    'patient_consultation_record_id' => $consultation->id,
                    'patient_name' => $consultation->full_name,
                    'age' => $consultation->age,
                    'department' => $consultation->department,
                    'student_employee_id' => $consultation->student_employee_id,
                    'contact_no' => $consultation->contact_no,
                    'entry_date_time' => $consultation->consultation_date_time->addHours(2),
                    'nurse_notes' => 'Patient responded well to initial treatment. Vital signs stable. Continue monitoring.',
                    'entered_by_nurse' => $consultation->nurse_on_duty,
                ]);
            }
        }
    }
}
