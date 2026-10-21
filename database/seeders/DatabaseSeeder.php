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
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. System Settings
        SystemSetting::set('barangay_name', 'Barangay San Isidro', 'Barangay Name');
        SystemSetting::set('municipality_city', 'Bay', 'Municipality / City');
        SystemSetting::set('province', 'Laguna', 'Province');
        SystemSetting::set('emergency_hotline', '0917-555-4321 / (049) 536-1234', 'Emergency Hotline');
        SystemSetting::set('utility_hotline', 'Meralco 16211 / Batelec (049) 555-0199', 'Electric Utility Hotline');
        SystemSetting::set('pdrrmc_hotline', 'Laguna PDRRMO 911 / (049) 501-1114', 'Disaster Hotline');

        // 2. Departments
        $deptPublicWorks = Department::create([
            'name' => 'Barangay Public Works & Infrastructure',
            'code' => 'BPWE',
            'description' => 'Handles roads, potholes, pavements, bridges, and public structure repairs.',
            'contact_person' => 'Engr. Roberto Dela Cruz',
            'contact_phone' => '0918-123-4567',
            'contact_email' => 'works@barangaysanisidro.gov.ph',
            'is_active' => true,
        ]);

        $deptElectric = Department::create([
            'name' => 'Utility & Electrical Coordination Desk',
            'code' => 'ELEC',
            'description' => 'Coordinates fallen electric cables, loose wires, transformers, and streetlight maintenance with power utilities.',
            'contact_person' => 'Arthur Villanueva (Utility Liaison)',
            'contact_phone' => '0920-333-7890',
            'contact_email' => 'electric@barangaysanisidro.gov.ph',
            'is_active' => true,
        ]);

        $deptDrainage = Department::create([
            'name' => 'Drainage, Canal & Flood Mitigation Unit',
            'code' => 'DRN',
            'description' => 'Oversees stormwater channels, unclogging open canals, and mitigating seasonal inundation.',
            'contact_person' => 'Mario Dimaculangan',
            'contact_phone' => '0919-888-2234',
            'contact_email' => 'drainage@barangaysanisidro.gov.ph',
            'is_active' => true,
        ]);

        $deptSanitation = Department::create([
            'name' => 'Environmental Sanitation & Cleanliness Taskforce',
            'code' => 'ENVR',
            'description' => 'Clears illegal dumpsites, collects roadside debris, and oversees tree trimming for hazards.',
            'contact_person' => 'Elena Bautista',
            'contact_phone' => '0922-444-9911',
            'contact_email' => 'sanitation@barangaysanisidro.gov.ph',
            'is_active' => true,
        ]);

        $deptBdrrmc = Department::create([
            'name' => 'Barangay Disaster Risk Reduction Committee (BDRRMC)',
            'code' => 'BDRRMC',
            'description' => 'Rapid response, hazard cordon, fallen tree cutting, and emergency community safety.',
            'contact_person' => 'Capt. Ferdinand Reyes',
            'contact_phone' => '0917-999-0022',
            'contact_email' => 'disaster@barangaysanisidro.gov.ph',
            'is_active' => true,
        ]);

        // 3. Report Categories
        $catRoad = ReportCategory::create([
            'name' => 'Damaged Roads, Potholes & Sidewalks',
            'slug' => 'damaged-roads-potholes',
            'description' => 'Deep potholes, broken concrete slabs, open manholes, or cracked pedestrian walkways.',
            'icon' => 'truck',
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
            'default_priority' => 'urgent',
            'department_id' => $deptElectric->id,
            'is_hazardous' => true,
            'safety_advisory' => 'CRITICAL SAFETY NOTICE: STAY AT LEAST 10 METERS (33 FEET) AWAY. Never attempt to touch, move, or drive over downed or sagging cables. Do not touch fences, water puddles, or trees in contact with electrical wires. This reporting system logs coordination with municipal liaisons; for immediate life-threatening situations, dial local emergency dispatch or Meralco (16211) immediately.',
            'is_active' => true,
        ]);

        $catDrainage = ReportCategory::create([
            'name' => 'Flooding, Clogged Drainage & Damaged Canals',
            'slug' => 'flooding-clogged-drainage',
            'description' => 'Canals overflowing with silt or trash, broken canal covers, and stagnant roadside floodwater.',
            'icon' => 'waves',
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
            'default_priority' => 'high',
            'department_id' => $deptBdrrmc->id,
            'is_hazardous' => true,
            'safety_advisory' => 'Exercise caution around leaning trees and unstable branches during gusty winds or heavy rainfall.',
            'is_active' => true,
        ]);

        // 4. Users
        $admin = User::create([
            'name' => 'Hon. Admin Captain Santos',
            'email' => 'admin@barangay.gov.ph',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '0917-100-2000',
            'barangay' => 'Barangay San Isidro',
            'address' => 'Barangay Hall, J.P. Rizal St., San Isidro',
            'is_active' => true,
        ]);

        $staffWorks = User::create([
            'name' => 'Officer Marco Reyes (Public Works)',
            'email' => 'staff.works@barangay.gov.ph',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'phone' => '0918-200-3000',
            'barangay' => 'Barangay San Isidro',
            'address' => 'Sitio Riverside, San Isidro',
            'department_id' => $deptPublicWorks->id,
            'is_active' => true,
        ]);

        $staffElectric = User::create([
            'name' => 'Officer Jaime Lopez (Electrical Liaison)',
            'email' => 'staff.electric@barangay.gov.ph',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'phone' => '0919-300-4000',
            'barangay' => 'Barangay San Isidro',
            'address' => 'Purok 3, San Isidro',
            'department_id' => $deptElectric->id,
            'is_active' => true,
        ]);

        $staffDrainage = User::create([
            'name' => 'Officer Corazon Dizon (Flood & Canal)',
            'email' => 'staff.drainage@barangay.gov.ph',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'phone' => '0920-400-5000',
            'barangay' => 'Barangay San Isidro',
            'address' => 'Purok 1, San Isidro',
            'department_id' => $deptDrainage->id,
            'is_active' => true,
        ]);

        $resident1 = User::create([
            'name' => 'Maria Clara Dela Santos',
            'email' => 'maria.santos@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'resident',
            'phone' => '0927-111-2233',
            'barangay' => 'Barangay San Isidro',
            'address' => 'Blk 4 Lot 12, Mahogany St., Villa Isidro Subd.',
            'is_active' => true,
        ]);

        $resident2 = User::create([
            'name' => 'Juan Pedro Del Rosario',
            'email' => 'juan.delacruz@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'resident',
            'phone' => '0935-444-5566',
            'barangay' => 'Barangay San Isidro',
            'address' => '142 Rizal Avenue, Purok 2',
            'is_active' => true,
        ]);

        // Ensure storage reports folder exists with a placeholder svg image
        Storage::disk('public')->makeDirectory('reports');
        $sampleSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400"><rect fill="#0f766e" width="600" height="400"/><text fill="#ffffff" font-family="sans-serif" font-size="24" font-weight="bold" x="50%" y="45%" text-anchor="middle">Barangay Damage Report Photo</text><text fill="#99f6e4" font-family="sans-serif" font-size="16" x="50%" y="55%" text-anchor="middle">Community Incident Documentation</text></svg>';
        Storage::disk('public')->put('reports/sample-damage.svg', $sampleSvg);

        // 5. Sample Reports
        // Report 1: Damaged Road / Pothole (In Progress)
        $rep1 = Report::create([
            'reference_number' => 'BRGY-20261021-0001',
            'user_id' => $resident1->id,
            'category_id' => $catRoad->id,
            'department_id' => $deptPublicWorks->id,
            'assigned_to' => $staffWorks->id,
            'title' => 'Deep pothole causing motorcycle accidents near Elementary School',
            'description' => 'A large deep pothole has expanded after the weekend rains directly along the southbound lane in front of San Isidro Elementary School. Several tricycles and motorbikes swerve dangerously into oncoming lanes.',
            'priority' => 'high',
            'status' => 'in_progress',
            'latitude' => 14.1804200,
            'longitude' => 121.2858100,
            'address_text' => 'National Highway in front of San Isidro Elementary School gate',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
            'landmark' => 'Directly across San Isidro Central School gate',
            'is_hazard_warning_acknowledged' => true,
            'submitted_at' => now()->subDays(3),
            'verified_at' => now()->subDays(2),
            'assigned_at' => now()->subDays(2),
            'in_progress_at' => now()->subDay(),
        ]);

        ReportImage::create([
            'report_id' => $rep1->id,
            'file_path' => 'reports/sample-damage.svg',
            'original_name' => 'pothole_school_lane.jpg',
            'mime_type' => 'image/svg+xml',
            'file_size' => 1024,
            'caption' => 'Deep pothole fronting school gate',
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
            'comment' => 'Inspected and confirmed by Barangay admin. Prioritized as high due to school zone traffic.',
            'created_at' => now()->subDays(2),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rep1->id,
            'user_id' => $admin->id,
            'from_status' => 'verified',
            'to_status' => 'assigned',
            'comment' => 'Assigned to Officer Marco Reyes (Public Works team).',
            'created_at' => now()->subDays(2),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rep1->id,
            'user_id' => $staffWorks->id,
            'from_status' => 'assigned',
            'to_status' => 'in_progress',
            'comment' => 'Cold-mix asphalt batch requisitioned; warning cones deployed around hole.',
            'created_at' => now()->subDay(),
        ]);

        ReportComment::create([
            'report_id' => $rep1->id,
            'user_id' => $admin->id,
            'comment' => 'Thank you for reporting, Ms. Maria. Barangay road maintenance crew has placed traffic cones and patching is scheduled tomorrow morning.',
            'is_internal' => false,
            'created_at' => now()->subDay(),
        ]);
        ReportComment::create([
            'report_id' => $rep1->id,
            'user_id' => $staffWorks->id,
            'comment' => 'Internal Note: 4 bags of asphalt patching compound dispatched with vehicle crew B.',
            'is_internal' => true,
            'created_at' => now()->subDay(),
        ]);

        Notification::create([
            'user_id' => $resident1->id,
            'report_id' => $rep1->id,
            'title' => 'Report In Progress',
            'message' => 'Your report BRGY-20261021-0001 (Deep pothole) has been placed In Progress by Public Works.',
            'type' => 'status_updated',
            'is_read' => false,
            'created_at' => now()->subDay(),
        ]);

        // Report 2: Fallen / Exposed Electrical Line (Urgent, Verified)
        $rep2 = Report::create([
            'reference_number' => 'BRGY-20261021-0002',
            'user_id' => $resident2->id,
            'category_id' => $catElectrical->id,
            'department_id' => $deptElectric->id,
            'assigned_to' => $staffElectric->id,
            'title' => 'Low hanging sparking electrical line dangling over pedestrian walkway',
            'description' => 'A primary secondary wire snapped off insulator during strong wind and is now dangling about 5 feet from the ground near a wooden fence. Sparks noticed when brushed by tree branches. Hazard tape required immediately.',
            'priority' => 'urgent',
            'status' => 'verified',
            'latitude' => 14.1831000,
            'longitude' => 121.2829000,
            'address_text' => 'Corner Sampaguita St. and Ilang-Ilang Lane, Purok 3',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
            'landmark' => 'Beside Mang Boy sari-sari store corner post',
            'is_hazard_warning_acknowledged' => true,
            'submitted_at' => now()->subHours(6),
            'verified_at' => now()->subHours(4),
            'assigned_at' => now()->subHours(4),
        ]);

        ReportImage::create([
            'report_id' => $rep2->id,
            'file_path' => 'reports/sample-damage.svg',
            'original_name' => 'sparking_wire_corner.jpg',
            'mime_type' => 'image/svg+xml',
            'file_size' => 1024,
            'caption' => 'Dangling power wire near wooden fence',
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
            'comment' => 'Verified as urgent electrical emergency coordination. Cordon crew dispatched.',
            'created_at' => now()->subHours(4),
        ]);

        ReportComment::create([
            'report_id' => $rep2->id,
            'user_id' => $staffElectric->id,
            'comment' => 'Barangay tanods have cordoned off the 10-meter perimeter with caution tape. Incident ticket #MER-2026-9921 lodged with Meralco emergency dispatch.',
            'is_internal' => false,
            'created_at' => now()->subHours(3),
        ]);

        Notification::create([
            'user_id' => $resident2->id,
            'report_id' => $rep2->id,
            'title' => 'Electrical Hazard Verified & Cordoned',
            'message' => 'Your report BRGY-20261021-0002 has been verified. Tanod response team has cordoned the area and contacted the power distributor.',
            'type' => 'status_updated',
            'is_read' => false,
            'created_at' => now()->subHours(3),
        ]);

        // Report 3: Clogged Drainage & Flooding (Resolved)
        $rep3 = Report::create([
            'reference_number' => 'BRGY-20261021-0003',
            'user_id' => $resident1->id,
            'category_id' => $catDrainage->id,
            'department_id' => $deptDrainage->id,
            'assigned_to' => $staffDrainage->id,
            'title' => 'Severe canal overflow submerging pedestrian walkway and driveway',
            'description' => 'The open canal along Purok 1 was choked with accumulated plastic and tree debris, causing muddy floodwater to spill into adjacent residential driveways even with moderate rain.',
            'priority' => 'high',
            'status' => 'resolved',
            'latitude' => 14.1789000,
            'longitude' => 121.2882000,
            'address_text' => 'Sitio Bukid Road canal junction, Purok 1',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
            'landmark' => 'Near water pump station',
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
            'user_id' => $staffDrainage->id,
            'from_status' => 'in_progress',
            'to_status' => 'resolved',
            'comment' => 'Canal desilted and debris collected by cleanup crew.',
            'created_at' => now()->subDay(),
        ]);

        // Report 4: Broken Streetlight (Submitted / Under Review)
        $rep4 = Report::create([
            'reference_number' => 'BRGY-20261021-0004',
            'user_id' => $resident2->id,
            'category_id' => $catStreetlight->id,
            'department_id' => null,
            'assigned_to' => null,
            'title' => 'LED street lamp unlit for 2 weeks in dark interior pathway',
            'description' => 'The pole-mounted street light near the basketball half-court has gone completely dark, leaving an 80-meter stretch unlit and unsafe for evening commuters.',
            'priority' => 'low',
            'status' => 'under_review',
            'latitude' => 14.1819500,
            'longitude' => 121.2843000,
            'address_text' => 'Path 4 beside community basketball half-court',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
            'landmark' => 'Beside Tanod outpost #2',
            'is_hazard_warning_acknowledged' => false,
            'submitted_at' => now()->subHours(12),
        ]);

        // Report 5: Fallen Tree (Assigned to BDRRMC)
        $rep5 = Report::create([
            'reference_number' => 'BRGY-20261021-0005',
            'user_id' => $resident1->id,
            'category_id' => $catTrees->id,
            'department_id' => $deptBdrrmc->id,
            'assigned_to' => null,
            'title' => 'Large acacia branch fractured and resting on perimeter wall',
            'description' => 'A heavy acacia branch broke partially during heavy thunderstorm winds and is precariously leaning on the chapel concrete perimeter wall right above the pedestrian sidewalk.',
            'priority' => 'high',
            'status' => 'assigned',
            'latitude' => 14.1824000,
            'longitude' => 121.2869000,
            'address_text' => 'Church Road corner San Isidro Chapel',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
            'landmark' => 'Adjacent to San Isidro Chapel garden',
            'is_hazard_warning_acknowledged' => true,
            'submitted_at' => now()->subDays(1),
            'verified_at' => now()->subHours(18),
            'assigned_at' => now()->subHours(16),
        ]);

        // Report 6: Duplicate Report (Duplicate linked to Report 1)
        Report::create([
            'reference_number' => 'BRGY-20261021-0006',
            'user_id' => $resident2->id,
            'category_id' => $catRoad->id,
            'department_id' => $deptPublicWorks->id,
            'duplicate_of_id' => $rep1->id,
            'title' => 'Big hole in front of elem school',
            'description' => 'Dangerous hole on the road near San Isidro Elementary gate.',
            'priority' => 'medium',
            'status' => 'duplicate',
            'latitude' => 14.1804500,
            'longitude' => 121.2858500,
            'address_text' => 'In front of elementary school',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
            'landmark' => 'Elementary school',
            'is_hazard_warning_acknowledged' => true,
            'submitted_at' => now()->subDays(2),
            'verified_at' => now()->subDays(2),
        ]);

        // Activity Logs
        ActivityLog::log('system_seed', 'Initial system seeding completed with realistic community data', null, ['seed_version' => '1.0'], $admin);
        ActivityLog::log('report_verified', "Report {$rep1->reference_number} verified and prioritized as High", $rep1, null, $admin);
        ActivityLog::log('report_assigned', "Report {$rep1->reference_number} assigned to Public Works", $rep1, null, $admin);
        ActivityLog::log('status_updated', "Report {$rep3->reference_number} marked as Resolved", $rep3, null, $staffDrainage);
    }
}
