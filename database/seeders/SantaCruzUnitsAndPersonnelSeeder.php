<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Personnel;
use Illuminate\Database\Seeder;

class SantaCruzUnitsAndPersonnelSeeder extends Seeder
{
    /**
     * Run the database seeds for Santa Cruz, Laguna Municipal Units and Personnel.
     */
    public function run(): void
    {
        $units = [
            [
                'code' => 'MDRRMO',
                'name' => 'Santa Cruz Municipal Disaster Risk Reduction & Management Office (MDRRMO)',
                'description' => 'Rapid incident response, hazard cordons, typhoon clearing operations, flood monitoring, and disaster preparedness.',
                'contact_person' => 'Capt. Ferdinand Reyes (MDRRMO Chief)',
                'contact_phone' => '0917-999-0022',
                'contact_email' => 'mdrrmo@santacruzlaguna.gov.ph',
                'is_active' => true,
                'personnel' => [
                    [
                        'name' => 'Capt. Ferdinand Reyes',
                        'position' => 'MDRRMO Chief / Emergency Operations Officer',
                        'contact_number' => '0917-999-0022',
                        'email' => 'mdrrmo@santacruzlaguna.gov.ph',
                    ],
                    [
                        'name' => 'Angelo Gomez',
                        'position' => 'Disaster Response Field Lead',
                        'contact_number' => '0917-999-0023',
                        'email' => 'agomez.mdrrmo@santacruzlaguna.gov.ph',
                    ],
                ],
            ],
            [
                'code' => 'MEPWO',
                'name' => 'Santa Cruz Municipal Engineering & Public Works Office',
                'description' => 'Handles municipal roads, potholes, pavements, drainage culverts, bridges, and public facility repairs.',
                'contact_person' => 'Engr. Roberto Dela Cruz (Municipal Engineer)',
                'contact_phone' => '0918-123-4567',
                'contact_email' => 'engineering@santacruzlaguna.gov.ph',
                'is_active' => true,
                'personnel' => [
                    [
                        'name' => 'Engr. Roberto Dela Cruz',
                        'position' => 'Municipal Engineer',
                        'contact_number' => '0918-123-4567',
                        'email' => 'engineering@santacruzlaguna.gov.ph',
                    ],
                    [
                        'name' => 'Dennis Alcantara',
                        'position' => 'Road Infrastructure Foreman',
                        'contact_number' => '0918-123-4568',
                        'email' => 'dalcantara.meo@santacruzlaguna.gov.ph',
                    ],
                ],
            ],
            [
                'code' => 'MHO',
                'name' => 'Santa Cruz Municipal Health Office',
                'description' => 'Community health surveillance, sanitation inspections, public health hazard response, and pest abatement.',
                'contact_person' => 'Dr. Maria Lourdes Santos (Municipal Health Officer)',
                'contact_phone' => '0919-456-7890',
                'contact_email' => 'health@santacruzlaguna.gov.ph',
                'is_active' => true,
                'personnel' => [
                    [
                        'name' => 'Dr. Maria Lourdes Santos',
                        'position' => 'Municipal Health Officer',
                        'contact_number' => '0919-456-7890',
                        'email' => 'health@santacruzlaguna.gov.ph',
                    ],
                    [
                        'name' => 'Clarissa Mendoza',
                        'position' => 'Sanitary Inspector II',
                        'contact_number' => '0919-456-7891',
                        'email' => 'cmendoza.mho@santacruzlaguna.gov.ph',
                    ],
                ],
            ],
            [
                'code' => 'MSWDO',
                'name' => 'Santa Cruz Municipal Social Welfare & Development Office',
                'description' => 'Crisis assistance, vulnerable family welfare, evacuation shelter management, and community outreach.',
                'contact_person' => 'Rosario Morales (Social Welfare Officer)',
                'contact_phone' => '0920-567-8901',
                'contact_email' => 'socialwelfare@santacruzlaguna.gov.ph',
                'is_active' => true,
                'personnel' => [
                    [
                        'name' => 'Rosario Morales',
                        'position' => 'Social Welfare Officer IV',
                        'contact_number' => '0920-567-8901',
                        'email' => 'socialwelfare@santacruzlaguna.gov.ph',
                    ],
                    [
                        'name' => 'Eduardo Lim',
                        'position' => 'Community Welfare Field Worker',
                        'contact_number' => '0920-567-8902',
                        'email' => 'elim.mswdo@santacruzlaguna.gov.ph',
                    ],
                ],
            ],
            [
                'code' => 'MENRO',
                'name' => 'Municipal Environment & Natural Resources Office (MENRO)',
                'description' => 'Clears illegal dumpsites, collects roadside debris, and oversees tree trimming for community hazards.',
                'contact_person' => 'Elena Bautista',
                'contact_phone' => '0922-444-9911',
                'contact_email' => 'menro@santacruzlaguna.gov.ph',
                'is_active' => true,
                'personnel' => [
                    [
                        'name' => 'Elena Bautista',
                        'position' => 'MENRO Officer',
                        'contact_number' => '0922-444-9911',
                        'email' => 'menro@santacruzlaguna.gov.ph',
                    ],
                    [
                        'name' => 'Carlos Ramos',
                        'position' => 'Solid Waste & Tree Pruning Supervisor',
                        'contact_number' => '0922-444-9912',
                        'email' => 'cramos.menro@santacruzlaguna.gov.ph',
                    ],
                ],
            ],
            [
                'code' => 'MPS',
                'name' => 'Santa Cruz Municipal Police Station (MPS - PNP)',
                'description' => 'Peace and order maintenance, patrol verification, crowd management, and public safety enforcement.',
                'contact_person' => 'PMAJ Rommel Castaneda (Chief of Police)',
                'contact_phone' => '0998-598-1234',
                'contact_email' => 'santacruz.mps@pnp.gov.ph',
                'is_active' => true,
                'personnel' => [
                    [
                        'name' => 'PMAJ Rommel Castaneda',
                        'position' => 'Chief of Police / Operations Head',
                        'contact_number' => '0998-598-1234',
                        'email' => 'santacruz.mps@pnp.gov.ph',
                    ],
                    [
                        'name' => 'PCpl Mark Villanueva',
                        'position' => 'Barangay Community Relations Officer',
                        'contact_number' => '0998-598-1235',
                        'email' => 'mvillanueva.mps@pnp.gov.ph',
                    ],
                ],
            ],
            [
                'code' => 'BFP',
                'name' => 'Bureau of Fire Protection - Santa Cruz Station',
                'description' => 'Fire prevention, electrical fire hazard investigation, and structural emergency assistance.',
                'contact_person' => 'CINSP Arnel Macaraig (Municipal Fire Marshal)',
                'contact_phone' => '0923-789-0123',
                'contact_email' => 'bfp.santacruz@laguna.gov.ph',
                'is_active' => true,
                'personnel' => [
                    [
                        'name' => 'CINSP Arnel Macaraig',
                        'position' => 'Municipal Fire Marshal',
                        'contact_number' => '0923-789-0123',
                        'email' => 'bfp.santacruz@laguna.gov.ph',
                    ],
                    [
                        'name' => 'FO3 Jonathan Navarro',
                        'position' => 'Fire Safety & Hazmat Inspector',
                        'contact_number' => '0923-789-0124',
                        'email' => 'jnavarro.bfp@laguna.gov.ph',
                    ],
                ],
            ],
            [
                'code' => 'BPAT',
                'name' => 'Santa Cruz Barangay Action & Peacekeeping Team (BPAT)',
                'description' => 'First-line village peacekeeping, barangay hall coordination, localized noise and dispute response.',
                'contact_person' => 'Virgilio Hernandez (BPAT Commander)',
                'contact_phone' => '0921-654-3210',
                'contact_email' => 'bpat@santacruzlaguna.gov.ph',
                'is_active' => true,
                'personnel' => [
                    [
                        'name' => 'Virgilio Hernandez',
                        'position' => 'Barangay Peacekeeping Commander',
                        'contact_number' => '0921-654-3210',
                        'email' => 'bpat@santacruzlaguna.gov.ph',
                    ],
                    [
                        'name' => 'Danilo Tolentino',
                        'position' => 'Field Action Officer',
                        'contact_number' => '0921-654-3211',
                        'email' => 'dtolentino.bpat@santacruzlaguna.gov.ph',
                    ],
                ],
            ],
            [
                'code' => 'TMU',
                'name' => 'Santa Cruz Traffic Management & Public Safety Unit',
                'description' => 'Traffic flow regulation, road obstruction towing, and intersection safety enforcement.',
                'contact_person' => 'Rolando Salazar (Traffic Operations Head)',
                'contact_phone' => '0916-234-5678',
                'contact_email' => 'tmu@santacruzlaguna.gov.ph',
                'is_active' => true,
                'personnel' => [
                    [
                        'name' => 'Rolando Salazar',
                        'position' => 'Traffic Operations Head',
                        'contact_number' => '0916-234-5678',
                        'email' => 'tmu@santacruzlaguna.gov.ph',
                    ],
                    [
                        'name' => 'Nestor Aquino',
                        'position' => 'Traffic Enforcement Team Leader',
                        'contact_number' => '0916-234-5679',
                        'email' => 'naquino.tmu@santacruzlaguna.gov.ph',
                    ],
                ],
            ],
            [
                'code' => 'USTF',
                'name' => 'Utility & Streetlighting Taskforce',
                'description' => 'Coordinates fallen electric cables, loose wires, transformers, and public streetlight maintenance.',
                'contact_person' => 'Arthur Villanueva (Utility Liaison)',
                'contact_phone' => '0920-333-7890',
                'contact_email' => 'streetlighting@santacruzlaguna.gov.ph',
                'is_active' => true,
                'personnel' => [
                    [
                        'name' => 'Arthur Villanueva',
                        'position' => 'Utility Liaison Officer',
                        'contact_number' => '0920-333-7890',
                        'email' => 'streetlighting@santacruzlaguna.gov.ph',
                    ],
                    [
                        'name' => 'Raymundo Flores',
                        'position' => 'Streetlight Maintenance Technician',
                        'contact_number' => '0920-333-7891',
                        'email' => 'rflores.ustf@santacruzlaguna.gov.ph',
                    ],
                ],
            ],
            [
                'code' => 'DCFM',
                'name' => 'Drainage, Canal & Flood Mitigation Unit',
                'description' => 'Oversees stormwater channels, unclogging open canals, and mitigating flooding near Laguna de Bay tributaries.',
                'contact_person' => 'Mario Dimaculangan',
                'contact_phone' => '0919-888-2234',
                'contact_email' => 'drainage@santacruzlaguna.gov.ph',
                'is_active' => true,
                'personnel' => [
                    [
                        'name' => 'Mario Dimaculangan',
                        'position' => 'Drainage Dredging Foreman',
                        'contact_number' => '0919-888-2234',
                        'email' => 'drainage@santacruzlaguna.gov.ph',
                    ],
                    [
                        'name' => 'Vicente Morales',
                        'position' => 'Canal De-siltation Crew Leader',
                        'contact_number' => '0919-888-2235',
                        'email' => 'vmorales.dcfm@santacruzlaguna.gov.ph',
                    ],
                ],
            ],
        ];

        foreach ($units as $unitData) {
            $personnelList = $unitData['personnel'] ?? [];
            unset($unitData['personnel']);

            $dept = Department::updateOrCreate(
                ['code' => $unitData['code']],
                $unitData
            );

            foreach ($personnelList as $pData) {
                Personnel::updateOrCreate(
                    [
                        'department_id' => $dept->id,
                        'name' => $pData['name'],
                    ],
                    [
                        'position' => $pData['position'],
                        'contact_number' => $pData['contact_number'],
                        'email' => $pData['email'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
