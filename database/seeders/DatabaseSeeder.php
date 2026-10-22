<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\ReportComment;
use App\Models\ReportImage;
use App\Models\ReportStatusHistory;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for Santa Cruz, Laguna, Philippines.
     */
    public function run(): void
    {
        // 1. System Settings (Config/Database Driven)
        SystemSetting::set('barangay_name', 'Poblacion I', 'Barangay Name');
        SystemSetting::set('municipality_city', config('community.municipality', 'Santa Cruz'), 'Municipality / City');
        SystemSetting::set('province', config('community.province', 'Laguna'), 'Province');
        SystemSetting::set('country', config('community.country', 'Philippines'), 'Country');
        SystemSetting::set('emergency_hotline', 'Santa Cruz MDRRMO (049) 501-6228 / 911', 'Emergency Hotline');
        SystemSetting::set('utility_hotline', 'Meralco Santa Cruz 16211 / Laguna Water (049) 559-0000', 'Electric/Water Hotline');
        SystemSetting::set('pdrrmc_hotline', 'Laguna Provincial Capitol PDRRMC (049) 501-1114', 'Disaster Hotline');

        // 2. Municipal Departments / Responsible Response Units
        $deptPublicWorks = Department::create([
            'name' => 'Santa Cruz Municipal Engineering & Public Works Office',
            'code' => 'MEPWO',
            'description' => 'Handles municipal roads, potholes, pavements, drainage culverts, bridges, and public facility repairs.',
            'contact_person' => 'Engr. Roberto Dela Cruz (Municipal Engineer)',
            'contact_phone' => '0918-123-4567',
            'contact_email' => 'engineering@santacruzlaguna.gov.ph',
            'is_active' => true,
        ]);

        $deptElectric = Department::create([
            'name' => 'Utility & Streetlighting Taskforce',
            'code' => 'USTF',
            'description' => 'Coordinates fallen electric cables, loose wires, transformers, and public streetlight maintenance.',
            'contact_person' => 'Arthur Villanueva (Utility Liaison)',
            'contact_phone' => '0920-333-7890',
            'contact_email' => 'streetlighting@santacruzlaguna.gov.ph',
            'is_active' => true,
        ]);

        $deptDrainage = Department::create([
            'name' => 'Drainage, Canal & Flood Mitigation Unit',
            'code' => 'DCFM',
            'description' => 'Oversees stormwater channels, unclogging open canals, and mitigating flooding near Laguna de Bay tributaries.',
            'contact_person' => 'Mario Dimaculangan',
            'contact_phone' => '0919-888-2234',
            'contact_email' => 'drainage@santacruzlaguna.gov.ph',
            'is_active' => true,
        ]);

        $deptSanitation = Department::create([
            'name' => 'Municipal Environment & Natural Resources Office (MENRO)',
            'code' => 'MENRO',
            'description' => 'Clears illegal dumpsites, collects roadside debris, and oversees tree trimming for community hazards.',
            'contact_person' => 'Elena Bautista',
            'contact_phone' => '0922-444-9911',
            'contact_email' => 'menro@santacruzlaguna.gov.ph',
            'is_active' => true,
        ]);

        $deptMdrrmo = Department::create([
            'name' => 'Santa Cruz Municipal Disaster Risk Reduction & Management Office (MDRRMO)',
            'code' => 'MDRRMO',
            'description' => 'Rapid incident response, hazard cordons, typhoon clearing operations, and public hazard safety.',
            'contact_person' => 'Capt. Ferdinand Reyes (MDRRMO Chief)',
            'contact_phone' => '0917-999-0022',
            'contact_email' => 'mdrrmo@santacruzlaguna.gov.ph',
            'is_active' => true,
        ]);

        // 3. Report Categories
        $catRoad = ReportCategory::create([
            'name' => 'Damaged Roads, Potholes & Sidewalks',
            'slug' => 'damaged-roads-potholes',
            'description' => 'Deep potholes, broken concrete slabs, open manholes, or cracked pedestrian walkways.',
            'icon' => 'truck',
            'color' => '#0F766E',
            'default_priority' => 'medium',
            'department_id' => $deptPublicWorks->id,
            'is_hazardous' => false,
            'safety_advisory' => null,
            'is_active' => true,
        ]);

        $catElectrical = ReportCategory::create([
            'name' => 'Fallen, Damaged, or Exposed Electrical Lines',
            'slug' => 'electrical-hazards-loose-wires',
            'description' => 'Low-hanging power lines, sparking transformers, leaning utility poles, or cables submerged in water.',
            'icon' => 'zap',
            'color' => '#E11D48',
            'default_priority' => 'urgent',
            'department_id' => $deptElectric->id,
            'is_hazardous' => true,
            'safety_advisory' => 'CRITICAL SAFETY NOTICE: STAY AT LEAST 10 METERS (33 FEET) AWAY. Never attempt to touch, move, or drive over downed or sagging cables. Do not touch fences, puddles, or trees in contact with electrical wires. This reporting system logs coordination with Santa Cruz utility liaisons; for immediate emergencies, dial Santa Cruz MDRRMO or Meralco (16211) immediately.',
            'is_active' => true,
        ]);

        $catDrainage = ReportCategory::create([
            'name' => 'Flooding, Clogged Drainage & Damaged Canals',
            'slug' => 'flooding-clogged-drainage',
            'description' => 'Canals overflowing with silt or trash, broken canal covers, and stagnant roadside floodwater.',
            'icon' => 'waves',
            'color' => '#D97706',
            'default_priority' => 'high',
            'department_id' => $deptDrainage->id,
            'is_hazardous' => false,
            'safety_advisory' => 'Avoid walking through floodwaters to prevent leptospirosis and unseen canal openings.',
            'is_active' => true,
        ]);

        $catStreetlight = ReportCategory::create([
            'name' => 'Broken Streetlights & Dark Alleys',
            'slug' => 'broken-streetlights',
            'description' => 'Non-functioning street lamps, flickering fixtures, or dark public walkways causing safety concerns.',
            'icon' => 'sun',
            'color' => '#DB2777',
            'default_priority' => 'low',
            'department_id' => $deptElectric->id,
            'is_hazardous' => false,
            'safety_advisory' => null,
            'is_active' => true,
        ]);

        $catFacility = ReportCategory::create([
            'name' => 'Damaged Public Facilities & Parks',
            'slug' => 'damaged-public-facilities',
            'description' => 'Vandalized barangay playground, damaged covered court roofing, broken perimeter fences or public benches.',
            'icon' => 'building',
            'color' => '#0F766E',
            'default_priority' => 'medium',
            'department_id' => $deptPublicWorks->id,
            'is_hazardous' => false,
            'safety_advisory' => null,
            'is_active' => true,
        ]);

        $catGarbage = ReportCategory::create([
            'name' => 'Garbage Accumulation & Illegal Dumping',
            'slug' => 'garbage-accumulation',
            'description' => 'Uncollected residential waste piles, unauthorized vacant lot trash dumping, and foul odor sources.',
            'icon' => 'trash-2',
            'color' => '#D97706',
            'default_priority' => 'medium',
            'department_id' => $deptSanitation->id,
            'is_hazardous' => false,
            'safety_advisory' => null,
            'is_active' => true,
        ]);

        $catTrees = ReportCategory::create([
            'name' => 'Fallen Trees & Dangerous Obstructions',
            'slug' => 'fallen-trees-obstructions',
            'description' => 'Uprooted tree branches blocking vehicular lanes, decayed trunks about to collapse, or highway debris.',
            'icon' => 'tree-pine',
            'color' => '#E11D48',
            'default_priority' => 'high',
            'department_id' => $deptMdrrmo->id,
            'is_hazardous' => true,
            'safety_advisory' => 'Exercise caution around leaning trees and unstable branches during gusty winds or heavy rainfall.',
            'is_active' => true,
        ]);

        // 4. Users (Admin and Residents)
        $admin = User::create([
            'name' => 'Hon. Admin Captain Santos',
            'email' => 'admin@barangay.gov.ph',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '0917-100-2000',
            'barangay' => 'Poblacion I',
            'address' => 'Santa Cruz Municipal Hall, Gen. Cailles St., Poblacion I',
            'is_active' => true,
        ]);

        $resident1 = User::create([
            'name' => 'Maria Clara Santos',
            'email' => 'maria.santos@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'resident',
            'phone' => '0927-111-2233',
            'barangay' => 'Bagumbayan',
            'address' => 'Blk 4 Lot 12, Mahogany St., Bagumbayan',
            'is_active' => true,
        ]);

        $resident2 = User::create([
            'name' => 'Juan Pedro Del Rosario',
            'email' => 'juan.delacruz@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'resident',
            'phone' => '0935-444-5566',
            'barangay' => 'Pagsawitan',
            'address' => '142 National Highway, Pagsawitan',
            'is_active' => true,
        ]);

        // Ensure storage reports folder exists with a placeholder svg image
        Storage::disk('public')->makeDirectory('reports');
        $sampleSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400"><rect fill="#0f766e" width="600" height="400"/><text fill="#ffffff" font-family="sans-serif" font-size="24" font-weight="bold" x="50%" y="45%" text-anchor="middle">Community Report Photo</text><text fill="#99f6e4" font-family="sans-serif" font-size="16" x="50%" y="55%" text-anchor="middle">Santa Cruz, Laguna Incident Documentation</text></svg>';
        Storage::disk('public')->put('reports/sample-damage.svg', $sampleSvg);

        // 5. Sample Reports (Accurately located across Santa Cruz, Laguna: ~14.28, ~121.41)
        // Report 1: Damaged Road / Pothole in Bagumbayan (In Progress)
        $rep1 = Report::create([
            'reference_number' => 'BRGY-20261021-0001',
            'user_id' => $resident1->id,
            'category_id' => $catRoad->id,
            'department_id' => $deptPublicWorks->id,
            'assigned_to' => $admin->id,
            'title' => 'Deep pothole causing motorcycle accidents along Pedro Guevara Ave',
            'description' => 'A large deep pothole has expanded after the weekend rains along the southbound lane of P. Guevara Ave. near Bagumbayan boundary. Several tricycles and motorbikes swerve dangerously into oncoming lanes.',
            'priority' => 'high',
            'status' => 'in_progress',
            'latitude' => 14.2835,
            'longitude' => 121.4168,
            'address_text' => 'Pedro Guevara Avenue near Bagumbayan welcome arch',
            'barangay' => 'Bagumbayan',
            'city' => 'Santa Cruz',
            'province' => 'Laguna',
            'landmark' => 'Near Bagumbayan boundary arch',
            'is_hazard_warning_acknowledged' => true,
            'submitted_at' => now()->subDays(3),
            'verified_at' => now()->subDays(2),
            'assigned_at' => now()->subDays(2),
            'in_progress_at' => now()->subDay(),
        ]);

        ReportImage::create([
            'report_id' => $rep1->id,
            'file_path' => 'reports/sample-damage.svg',
            'original_name' => 'pothole_guevara_ave.jpg',
            'mime_type' => 'image/svg+xml',
            'file_size' => 1024,
            'caption' => 'Deep pothole along southbound lane',
        ]);

        ReportStatusHistory::create([
            'report_id' => $rep1->id,
            'user_id' => $resident1->id,
            'from_status' => null,
            'to_status' => 'submitted',
            'comment' => 'Report filed by resident.',
            'created_at' => now()->subDays(3),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rep1->id,
            'user_id' => $admin->id,
            'from_status' => 'submitted',
            'to_status' => 'verified',
            'comment' => 'Inspected and confirmed by Municipal Engineering. Prioritized due to high-volume commuter traffic.',
            'created_at' => now()->subDays(2),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rep1->id,
            'user_id' => $admin->id,
            'from_status' => 'verified',
            'to_status' => 'assigned',
            'comment' => 'Assigned to Public Works road maintenance crew.',
            'created_at' => now()->subDays(2),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rep1->id,
            'user_id' => $admin->id,
            'from_status' => 'assigned',
            'to_status' => 'in_progress',
            'comment' => 'Cold-mix asphalt batch requisitioned; warning cones deployed around hole.',
            'created_at' => now()->subDay(),
        ]);

        ReportComment::create([
            'report_id' => $rep1->id,
            'user_id' => $admin->id,
            'comment' => 'Thank you for reporting, Ms. Maria. Municipal road crew has placed safety barriers and patching is scheduled tomorrow morning.',
            'is_internal' => false,
            'created_at' => now()->subDay(),
        ]);
        ReportComment::create([
            'report_id' => $rep1->id,
            'user_id' => $admin->id,
            'comment' => 'Internal Note: 4 bags of asphalt patching compound dispatched with vehicle crew B.',
            'is_internal' => true,
            'created_at' => now()->subDay(),
        ]);

        Notification::create([
            'user_id' => $resident1->id,
            'report_id' => $rep1->id,
            'title' => 'Report In Progress',
            'message' => 'Your report BRGY-20261021-0001 (Deep pothole) has been placed In Progress by Municipal Public Works.',
            'type' => 'status_updated',
            'is_read' => false,
            'created_at' => now()->subDay(),
        ]);

        // Report 2: Fallen / Exposed Electrical Line in Poblacion IV (Urgent, Verified)
        $rep2 = Report::create([
            'reference_number' => 'BRGY-20261021-0002',
            'user_id' => $resident2->id,
            'category_id' => $catElectrical->id,
            'department_id' => $deptElectric->id,
            'assigned_to' => $admin->id,
            'title' => 'Low hanging sparking electrical line dangling over pedestrian walkway',
            'description' => 'A secondary wire snapped off an insulator during gusty winds and is now dangling about 5 feet from the ground along A. Mabini Street near the corner school. Sparks observed when brushed by tree branches.',
            'priority' => 'urgent',
            'status' => 'verified',
            'latitude' => 14.2818,
            'longitude' => 121.4132,
            'address_text' => 'A. Mabini St. corner Regidor St., Poblacion IV',
            'barangay' => 'Poblacion IV',
            'city' => 'Santa Cruz',
            'province' => 'Laguna',
            'landmark' => 'Across corner bakery post',
            'is_hazard_warning_acknowledged' => true,
            'submitted_at' => now()->subHours(6),
            'verified_at' => now()->subHours(4),
            'assigned_at' => now()->subHours(4),
        ]);

        ReportImage::create([
            'report_id' => $rep2->id,
            'file_path' => 'reports/sample-damage.svg',
            'original_name' => 'sparking_wire_mabini.jpg',
            'mime_type' => 'image/svg+xml',
            'file_size' => 1024,
            'caption' => 'Dangling power wire near pedestrian walk',
        ]);

        ReportStatusHistory::create([
            'report_id' => $rep2->id,
            'user_id' => $resident2->id,
            'from_status' => null,
            'to_status' => 'submitted',
            'comment' => 'Urgent electrical hazard filed.',
            'created_at' => now()->subHours(6),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rep2->id,
            'user_id' => $admin->id,
            'from_status' => 'submitted',
            'to_status' => 'verified',
            'comment' => 'Verified as urgent electrical emergency. MDRRMO cordon crew dispatched.',
            'created_at' => now()->subHours(4),
        ]);

        ReportComment::create([
            'report_id' => $rep2->id,
            'user_id' => $admin->id,
            'comment' => 'Barangay tanods and MDRRMO have cordoned off the 10-meter perimeter with caution tape. Incident ticket #MER-2026-9921 lodged with Meralco emergency dispatch.',
            'is_internal' => false,
            'created_at' => now()->subHours(3),
        ]);

        Notification::create([
            'user_id' => $resident2->id,
            'report_id' => $rep2->id,
            'title' => 'Electrical Hazard Verified & Cordoned',
            'message' => 'Your report BRGY-20261021-0002 has been verified. Response team has cordoned the area and contacted the power distributor.',
            'type' => 'status_updated',
            'is_read' => false,
            'created_at' => now()->subHours(3),
        ]);

        // Report 3: Clogged Drainage & Flooding in Santisima Cruz (Resolved)
        $rep3 = Report::create([
            'reference_number' => 'BRGY-20261021-0003',
            'user_id' => $resident1->id,
            'category_id' => $catDrainage->id,
            'department_id' => $deptDrainage->id,
            'assigned_to' => $admin->id,
            'title' => 'Severe canal overflow submerging access road to Santisima Cruz',
            'description' => 'The open canal along Santisima Cruz was choked with accumulated plastic and silt, causing muddy floodwater to spill into adjacent residential driveways even with moderate rain.',
            'priority' => 'high',
            'status' => 'resolved',
            'latitude' => 14.2774,
            'longitude' => 121.4190,
            'address_text' => 'Santisima Cruz main access road junction',
            'barangay' => 'Santisima Cruz',
            'city' => 'Santa Cruz',
            'province' => 'Laguna',
            'landmark' => 'Near Santisima Cruz barangay outpost',
            'is_hazard_warning_acknowledged' => true,
            'submitted_at' => now()->subDays(5),
            'verified_at' => now()->subDays(4),
            'assigned_at' => now()->subDays(4),
            'in_progress_at' => now()->subDays(3),
            'resolved_at' => now()->subDay(),
            'resolution_notes' => 'Drainage crew manually desilted 80 meters of concrete culvert and hauled 12 sacks of trash. Water flow fully restored with no standing water.',
            'resolution_image_path' => 'reports/sample-damage.svg',
        ]);

        ReportImage::create([
            'report_id' => $rep3->id,
            'file_path' => 'reports/sample-damage.svg',
            'original_name' => 'canal_overflow.jpg',
            'mime_type' => 'image/svg+xml',
            'file_size' => 1024,
            'caption' => 'Initial flooded canal condition',
        ]);
        ReportImage::create([
            'report_id' => $rep3->id,
            'file_path' => 'reports/sample-damage.svg',
            'original_name' => 'canal_cleared_post.jpg',
            'mime_type' => 'image/svg+xml',
            'file_size' => 1024,
            'is_resolution_proof' => true,
            'caption' => 'Post-resolution clear water flow',
        ]);

        ReportStatusHistory::create([
            'report_id' => $rep3->id,
            'user_id' => $resident1->id,
            'from_status' => null,
            'to_status' => 'submitted',
            'comment' => 'Report filed by resident.',
            'created_at' => now()->subDays(5),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rep3->id,
            'user_id' => $admin->id,
            'from_status' => 'in_progress',
            'to_status' => 'resolved',
            'comment' => 'Canal desilted and debris collected by municipal drainage crew.',
            'created_at' => now()->subDay(),
        ]);

        // Report 4: Broken Streetlight in Pagsawitan (Submitted / Under Review)
        Report::create([
            'reference_number' => 'BRGY-20261021-0004',
            'user_id' => $resident2->id,
            'category_id' => $catStreetlight->id,
            'department_id' => null,
            'assigned_to' => null,
            'title' => 'LED street lamp unlit for 2 weeks along Pagsawitan riverside road',
            'description' => 'The pole-mounted street light near the river dike pathway has gone completely dark, leaving an 80-meter stretch unlit and unsafe for evening commuters walking from the bus stop.',
            'priority' => 'low',
            'status' => 'under_review',
            'latitude' => 14.2862,
            'longitude' => 121.4241,
            'address_text' => 'Pagsawitan riverside dike path',
            'barangay' => 'Pagsawitan',
            'city' => 'Santa Cruz',
            'province' => 'Laguna',
            'landmark' => 'Near Pagsawitan river dike gate',
            'is_hazard_warning_acknowledged' => false,
            'submitted_at' => now()->subHours(12),
        ]);

        // Report 5: Fallen Tree in Bubukal (Assigned to MDRRMO)
        Report::create([
            'reference_number' => 'BRGY-20261021-0005',
            'user_id' => $resident1->id,
            'category_id' => $catTrees->id,
            'department_id' => $deptMdrrmo->id,
            'assigned_to' => $admin->id,
            'title' => 'Large mango tree branch fractured and resting on perimeter fence',
            'description' => 'A heavy mango tree branch broke partially during heavy thunderstorm winds and is precariously leaning on a residential perimeter fence right above the sidewalk in Bubukal.',
            'priority' => 'high',
            'status' => 'assigned',
            'latitude' => 14.2690,
            'longitude' => 121.4115,
            'address_text' => 'Bubukal Provincial Road near boundary',
            'barangay' => 'Bubukal',
            'city' => 'Santa Cruz',
            'province' => 'Laguna',
            'landmark' => 'Near Bubukal elementary school',
            'is_hazard_warning_acknowledged' => true,
            'submitted_at' => now()->subDays(1),
            'verified_at' => now()->subHours(18),
            'assigned_at' => now()->subHours(16),
        ]);

        // Report 6: Duplicate Report in Bagumbayan
        Report::create([
            'reference_number' => 'BRGY-20261021-0006',
            'user_id' => $resident2->id,
            'category_id' => $catRoad->id,
            'department_id' => $deptPublicWorks->id,
            'duplicate_of_id' => $rep1->id,
            'title' => 'Big hole in front of Bagumbayan arch',
            'description' => 'Dangerous hole on the road near Bagumbayan boundary.',
            'priority' => 'medium',
            'status' => 'duplicate',
            'latitude' => 14.2836,
            'longitude' => 121.4169,
            'address_text' => 'Pedro Guevara Ave., Bagumbayan',
            'barangay' => 'Bagumbayan',
            'city' => 'Santa Cruz',
            'province' => 'Laguna',
            'landmark' => 'Bagumbayan boundary arch',
            'is_hazard_warning_acknowledged' => true,
            'submitted_at' => now()->subDays(2),
            'verified_at' => now()->subDays(2),
        ]);

        // Activity Logs
        ActivityLog::log('system_seed', 'Initial system seeding completed with Santa Cruz, Laguna municipal data', null, ['seed_version' => '2.0', 'municipality' => 'Santa Cruz, Laguna'], $admin);
        ActivityLog::log('report_verified', "Report {$rep1->reference_number} verified and prioritized as High", $rep1, null, $admin);
        ActivityLog::log('report_assigned', "Report {$rep1->reference_number} assigned to Municipal Engineering", $rep1, null, $admin);
        ActivityLog::log('status_updated', "Report {$rep3->reference_number} marked as Resolved", $rep3, null, $admin);
    }
}
