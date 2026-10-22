import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../auth/presentation/auth_provider.dart';
import '../../reports/domain/report_models.dart';
import '../../reports/presentation/my_reports_screen.dart';
import '../../reports/presentation/report_detail_screen.dart';
import '../../reports/presentation/report_form_screen.dart';
import '../../reports/presentation/report_provider.dart';

class HomeScreen extends StatefulWidget {
  final VoidCallback onNavigateToMyReports;

  const HomeScreen({super.key, required this.onNavigateToMyReports});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  CommunityPinModel? _selectedPin;

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final reportProvider = context.watch<ReportProvider>();
    final user = auth.user;
    final stats = reportProvider.stats;
    final pins = reportProvider.communityPins;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    // Default center: Santa Cruz, Laguna Town Hall / Municipal Center (~14.2816, 121.4146)
    final mapCenter = pins.isNotEmpty
        ? LatLng(pins.first.latitude, pins.first.longitude)
        : const LatLng(14.2816, 121.4146);

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Community Report App',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            Text(
              user?.barangay != null
                  ? '${user!.barangay}, Santa Cruz'
                  : 'Santa Cruz, Laguna',
              style: Theme.of(context).textTheme.bodySmall,
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Refresh Data',
            onPressed: () {
              HapticFeedback.lightImpact();
              auth.refreshProfile();
              reportProvider.loadInitialData();
            },
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () async {
            await Future.wait([
              auth.refreshProfile(),
              reportProvider.loadInitialData(),
            ]);
          },
          child: LayoutBuilder(
            builder: (context, constraints) {
              final maxWidth =
                  constraints.maxWidth > 860 ? 820.0 : double.infinity;

              return Center(
                child: SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.all(AppTheme.s16),
                  child: ConstrainedBox(
                    constraints: BoxConstraints(maxWidth: maxWidth),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // BENTO TILE 1 (Large Top Tile): Embedded Live Mini-Map
                        Card(
                          clipBehavior: Clip.antiAlias,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              Padding(
                                padding: const EdgeInsets.fromLTRB(
                                  AppTheme.s16,
                                  AppTheme.s12,
                                  AppTheme.s16,
                                  AppTheme.s8,
                                ),
                                child: Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  children: [
                                    Row(
                                      children: [
                                        Container(
                                          width: 8,
                                          height: 8,
                                          decoration: const BoxDecoration(
                                            color: AppTheme.statusResolvedDot,
                                            shape: BoxShape.circle,
                                          ),
                                        ),
                                        const SizedBox(width: 8),
                                        Text(
                                          'Live Community Map',
                                          style: Theme.of(context)
                                              .textTheme
                                              .titleMedium,
                                        ),
                                      ],
                                    ),
                                    Text(
                                      '${pins.length} reported nearby',
                                      style:
                                          Theme.of(context).textTheme.bodySmall,
                                    ),
                                  ],
                                ),
                              ),
                              SizedBox(
                                height: 210,
                                child: Stack(
                                  children: [
                                    FlutterMap(
                                      options: MapOptions(
                                        initialCenter: mapCenter,
                                        initialZoom: 14.8,
                                        onTap: (_, _) {
                                          if (_selectedPin != null) {
                                            setState(() {
                                              _selectedPin = null;
                                            });
                                          }
                                        },
                                      ),
                                      children: [
                                        TileLayer(
                                          urlTemplate:
                                              'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                                          userAgentPackageName:
                                              'ph.gov.barangay.community_report_app',
                                        ),
                                        MarkerLayer(
                                          markers: pins.map((pin) {
                                            final token =
                                                AppTheme.resolveStatusToken(
                                              status: pin.status,
                                              priority: pin.priority,
                                              isDark: isDark,
                                            );
                                            return Marker(
                                              point: LatLng(
                                                pin.latitude,
                                                pin.longitude,
                                              ),
                                              width: 36,
                                              height: 36,
                                              child: GestureDetector(
                                                onTap: () {
                                                  HapticFeedback
                                                      .selectionClick();
                                                  setState(() {
                                                    _selectedPin = pin;
                                                  });
                                                },
                                                child: Center(
                                                  child: Container(
                                                    width: 18,
                                                    height: 18,
                                                    decoration: BoxDecoration(
                                                      color: token.dot,
                                                      shape: BoxShape.circle,
                                                      border: Border.all(
                                                        color: Colors.white,
                                                        width: 2.5,
                                                      ),
                                                    ),
                                                  ),
                                                ),
                                              ),
                                            );
                                          }).toList(),
                                        ),
                                      ],
                                    ),
                                    if (_selectedPin != null)
                                      Positioned(
                                        left: 12,
                                        right: 12,
                                        bottom: 12,
                                        child: Dismissible(
                                          key: ValueKey(_selectedPin!.id),
                                          onDismissed: (_) {
                                            setState(() {
                                              _selectedPin = null;
                                            });
                                          },
                                          child: Container(
                                            padding: const EdgeInsets.all(10),
                                            decoration: BoxDecoration(
                                              color: isDark
                                                  ? AppTheme.slate900
                                                  : Colors.white,
                                              borderRadius:
                                                  BorderRadius.circular(
                                                AppTheme.r10,
                                              ),
                                              border: Border.all(
                                                color: isDark
                                                    ? AppTheme.slate700
                                                    : AppTheme.slate200,
                                              ),
                                            ),
                                            child: Row(
                                              children: [
                                                Expanded(
                                                  child: Column(
                                                    crossAxisAlignment:
                                                        CrossAxisAlignment
                                                            .start,
                                                    mainAxisSize:
                                                        MainAxisSize.min,
                                                    children: [
                                                      Text(
                                                        _selectedPin!.title,
                                                        maxLines: 1,
                                                        overflow: TextOverflow
                                                            .ellipsis,
                                                        style: const TextStyle(
                                                          fontWeight:
                                                              FontWeight.w700,
                                                          fontSize: 13,
                                                        ),
                                                      ),
                                                      const SizedBox(height: 2),
                                                      Text(
                                                        '${_selectedPin!.categoryName} • ${_selectedPin!.addressText}',
                                                        maxLines: 1,
                                                        overflow: TextOverflow
                                                            .ellipsis,
                                                        style: Theme.of(context)
                                                            .textTheme
                                                            .bodySmall,
                                                      ),
                                                    ],
                                                  ),
                                                ),
                                                const SizedBox(width: 8),
                                                StatusPill(
                                                  status: _selectedPin!.status,
                                                  customLabel: _selectedPin!
                                                      .statusLabel,
                                                  priority:
                                                      _selectedPin!.priority,
                                                ),
                                              ],
                                            ),
                                          ),
                                        ),
                                      ),
                                  ],
                                ),
                              ),
                              // Map Status Token Legend
                              Padding(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: AppTheme.s12,
                                  vertical: 8,
                                ),
                                child: Wrap(
                                  spacing: 14,
                                  runSpacing: 6,
                                  children: const [
                                    _LegendDotItem(
                                      color: AppTheme.statusHazardDot,
                                      label: 'Critical Hazard',
                                    ),
                                    _LegendDotItem(
                                      color: AppTheme.statusProgressDot,
                                      label: 'In Progress',
                                    ),
                                    _LegendDotItem(
                                      color: AppTheme.statusLowDot,
                                      label: 'Low Priority',
                                    ),
                                    _LegendDotItem(
                                      color: AppTheme.statusResolvedDot,
                                      label: 'Resolved',
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: AppTheme.s12),

                        // BENTO TILE 2 (Primary High-Contrast CTA Tile): "Report an Issue"
                        Material(
                          color: isDark
                              ? const Color(0xFF14B8A6)
                              : AppTheme.slate900,
                          borderRadius:
                              BorderRadius.circular(AppTheme.radiusBento),
                          child: InkWell(
                            borderRadius:
                                BorderRadius.circular(AppTheme.radiusBento),
                            onTap: () => showReportIssueBottomSheet(context),
                            child: Padding(
                              padding: const EdgeInsets.all(AppTheme.s20),
                              child: Row(
                                children: [
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          'Report an Issue',
                                          style: TextStyle(
                                            fontFamily: AppTheme.fontFamily,
                                            fontSize: 21,
                                            fontWeight: FontWeight.w800,
                                            letterSpacing: -0.4,
                                            color: isDark
                                                ? AppTheme.slate950
                                                : Colors.white,
                                          ),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          'Open the quick sheet to snap a photo, drop a map pin, and alert the barangay.',
                                          style: TextStyle(
                                            fontFamily: AppTheme.fontFamily,
                                            fontSize: 13,
                                            fontWeight: FontWeight.w500,
                                            color: isDark
                                                ? AppTheme.slate900
                                                : AppTheme.slate300,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  const SizedBox(width: AppTheme.s16),
                                  Container(
                                    width: 52,
                                    height: 52,
                                    decoration: BoxDecoration(
                                      color: isDark
                                          ? AppTheme.slate950
                                          : AppTheme.primaryTeal,
                                      borderRadius:
                                          BorderRadius.circular(AppTheme.r10),
                                    ),
                                    child: Icon(
                                      Icons.add_a_photo_outlined,
                                      color: Colors.white,
                                      size: 26,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: AppTheme.s12),

                        // BENTO ROW 3 (Asymmetrical Smaller Tiles: My Active Reports + Local Safety Alerts)
                        // Note: "Community Announcements" is intentionally hidden because no backend announcements table exists yet.
                        IntrinsicHeight(
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              // Tile 3A: My Active Reports (Count)
                              Expanded(
                                flex: 6,
                                child: Card(
                                  child: InkWell(
                                    borderRadius: BorderRadius.circular(
                                      AppTheme.radiusBento,
                                    ),
                                    onTap: widget.onNavigateToMyReports,
                                    child: Padding(
                                      padding:
                                          const EdgeInsets.all(AppTheme.s16),
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        mainAxisAlignment:
                                            MainAxisAlignment.spaceBetween,
                                        children: [
                                          Row(
                                            mainAxisAlignment:
                                                MainAxisAlignment.spaceBetween,
                                            children: [
                                              const StatusPill(
                                                status: 'in_progress',
                                                customLabel: 'My Active',
                                              ),
                                              Icon(
                                                Icons.arrow_forward_rounded,
                                                size: 18,
                                                color: Theme.of(context)
                                                    .textTheme
                                                    .bodySmall
                                                    ?.color,
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: AppTheme.s12),
                                          Text(
                                            stats.active.toString(),
                                            style: Theme.of(context)
                                                .textTheme
                                                .headlineMedium,
                                          ),
                                          const SizedBox(height: 2),
                                          Text(
                                            'My Active Reports (${stats.total} total)',
                                            style: Theme.of(context)
                                                .textTheme
                                                .bodySmall,
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(width: AppTheme.s12),

                              // Tile 3B: Local Safety Alerts (Live Urgent Hazard Count from API)
                              Expanded(
                                flex: 5,
                                child: Card(
                                  child: Padding(
                                    padding:
                                        const EdgeInsets.all(AppTheme.s16),
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      mainAxisAlignment:
                                          MainAxisAlignment.spaceBetween,
                                      children: [
                                        const PriorityPill(priority: 'urgent'),
                                        const SizedBox(height: AppTheme.s12),
                                        Text(
                                          stats.safetyAlerts.toString(),
                                          style: Theme.of(context)
                                              .textTheme
                                              .headlineMedium,
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          'Local Safety Alerts',
                                          style: Theme.of(context)
                                              .textTheme
                                              .bodySmall,
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: AppTheme.s20),

                        // Quick Category Chips (Opens Bottom Sheet pre-selected)
                        if (reportProvider.categories.isNotEmpty) ...[
                          Text(
                            'Quick Report by Category',
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                          const SizedBox(height: AppTheme.s8),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children:
                                reportProvider.categories.take(6).map((cat) {
                              return ActionChip(
                                avatar: Container(
                                  width: 8,
                                  height: 8,
                                  decoration: BoxDecoration(
                                    color: cat.isHazardous
                                        ? AppTheme.statusHazardDot
                                        : AppTheme.statusProgressDot,
                                    shape: BoxShape.circle,
                                  ),
                                ),
                                label: Text(cat.name),
                                onPressed: () => showReportIssueBottomSheet(
                                  context,
                                  initialCategory: cat,
                                ),
                              );
                            }).toList(),
                          ),
                          const SizedBox(height: AppTheme.s20),
                        ],

                        // Recent Reports List
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'My Recent Reports',
                              style: Theme.of(context).textTheme.titleMedium,
                            ),
                            TextButton(
                              onPressed: widget.onNavigateToMyReports,
                              child: const Text('View All'),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        if (reportProvider.isLoadingList &&
                            reportProvider.reports.isEmpty)
                          const Padding(
                            padding: EdgeInsets.all(AppTheme.s24),
                            child: Center(child: CircularProgressIndicator()),
                          )
                        else if (reportProvider.reports.isEmpty)
                          Card(
                            child: Padding(
                              padding: const EdgeInsets.all(AppTheme.s24),
                              child: Column(
                                children: [
                                  Text(
                                    'No reports submitted yet',
                                    style:
                                        Theme.of(context).textTheme.titleMedium,
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    'Tap "Report an Issue" above to open the quick report sheet.',
                                    textAlign: TextAlign.center,
                                    style:
                                        Theme.of(context).textTheme.bodySmall,
                                  ),
                                ],
                              ),
                            ),
                          )
                        else
                          ...reportProvider.reports.take(3).map((report) {
                            return Padding(
                              padding: const EdgeInsets.only(bottom: 10),
                              child: InkWell(
                                borderRadius: BorderRadius.circular(
                                  AppTheme.radiusBento,
                                ),
                                onTap: () {
                                  Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) => ReportDetailScreen(
                                        reportId: report.id,
                                      ),
                                    ),
                                  );
                                },
                                child: ReportCardItem(report: report),
                              ),
                            );
                          }),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      ),
    );
  }
}

class _LegendDotItem extends StatelessWidget {
  final Color color;
  final String label;

  const _LegendDotItem({required this.color, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 8,
          height: 8,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
        const SizedBox(width: 5),
        Text(
          label,
          style: Theme.of(context).textTheme.bodySmall?.copyWith(fontSize: 11),
        ),
      ],
    );
  }
}
