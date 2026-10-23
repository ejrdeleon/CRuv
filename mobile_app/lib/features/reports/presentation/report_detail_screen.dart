import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:intl/intl.dart';
import 'package:latlong2/latlong.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_theme.dart';
import 'report_provider.dart';
import 'widgets/report_photo.dart';

class ReportDetailScreen extends StatefulWidget {
  final int reportId;

  const ReportDetailScreen({super.key, required this.reportId});

  @override
  State<ReportDetailScreen> createState() => _ReportDetailScreenState();
}

class _ReportDetailScreenState extends State<ReportDetailScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ReportProvider>().loadReportDetail(widget.reportId);
    });
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'resolved':
        return AppTheme.statusResolvedDot;
      case 'rejected':
      case 'duplicate':
        return AppTheme.statusHazardDot;
      default:
        return AppTheme.statusProgressDot;
    }
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ReportProvider>();
    final report = provider.selectedReport;

    return Scaffold(
      appBar: AppBar(
        title: Text(
          report != null && report.id == widget.reportId
              ? report.referenceNumber
              : 'Report Details',
        ),
        actions: [
          IconButton(
            tooltip: 'Refresh Status',
            onPressed: () => provider.loadReportDetail(widget.reportId),
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: SafeArea(
        child: Builder(
          builder: (context) {
            if (provider.isLoadingDetail &&
                (report == null || report.id != widget.reportId)) {
              return const Center(child: CircularProgressIndicator());
            }

            if (provider.errorMessage != null &&
                (report == null || report.id != widget.reportId)) {
              return Center(
                child: Padding(
                  padding: const EdgeInsets.all(AppTheme.spacingLg),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        provider.isOffline
                            ? Icons.wifi_off_outlined
                            : Icons.error_outline,
                        size: 48,
                        color: AppTheme.textSecondary,
                      ),
                      const SizedBox(height: AppTheme.spacingMd),
                      Text(
                        provider.errorMessage!,
                        textAlign: TextAlign.center,
                        style: Theme.of(context).textTheme.bodyMedium,
                      ),
                      const SizedBox(height: AppTheme.spacingMd),
                      ElevatedButton.icon(
                        onPressed: () =>
                            provider.loadReportDetail(widget.reportId),
                        icon: const Icon(Icons.refresh),
                        label: const Text('Try Again'),
                      ),
                    ],
                  ),
                ),
              );
            }

            if (report == null) {
              return const Center(child: Text('Report not found.'));
            }

            final dateFormat = DateFormat('MMM d, yyyy • h:mm a');

            return RefreshIndicator(
              onRefresh: () => provider.loadReportDetail(widget.reportId),
              child: LayoutBuilder(
                builder: (context, constraints) {
                  final maxWidth =
                      constraints.maxWidth > 760 ? 720.0 : double.infinity;

                  return Center(
                    child: SingleChildScrollView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.all(AppTheme.spacingMd),
                      child: ConstrainedBox(
                        constraints: BoxConstraints(maxWidth: maxWidth),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            // Header Card
                            Card(
                              child: Padding(
                                padding:
                                    const EdgeInsets.all(AppTheme.spacingMd),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Wrap(
                                      spacing: 8,
                                      runSpacing: 8,
                                      crossAxisAlignment:
                                          WrapCrossAlignment.center,
                                      children: [
                                        StatusPill(
                                          status: report.status,
                                          customLabel: report.statusLabel,
                                          priority: report.priority,
                                        ),
                                        PriorityPill(priority: report.priority),
                                        if (report.category != null)
                                          Chip(
                                            label: Text(report.category!.name),
                                            visualDensity:
                                                VisualDensity.compact,
                                          ),
                                      ],
                                    ),
                                    const SizedBox(height: AppTheme.spacingSm),
                                    Text(
                                      report.title,
                                      style: Theme.of(context)
                                          .textTheme
                                          .titleLarge,
                                    ),
                                    const SizedBox(height: 6),
                                    if (report.createdAt != null)
                                      Text(
                                        'Submitted on ${dateFormat.format(report.createdAt!.toLocal())}',
                                        style: Theme.of(context)
                                            .textTheme
                                            .bodySmall,
                                      ),
                                    const Divider(height: 24),
                                    Text(
                                      report.description,
                                      style: Theme.of(context)
                                          .textTheme
                                          .bodyLarge,
                                    ),
                                    if (report.assignedToName != null) ...[
                                      const SizedBox(
                                        height: AppTheme.spacingMd,
                                      ),
                                      Row(
                                        children: [
                                          Icon(
                                            Icons.badge_outlined,
                                            size: 18,
                                            color: Theme.of(context).colorScheme.primary,
                                          ),
                                          const SizedBox(width: 6),
                                          Expanded(
                                            child: Text(
                                              'Assigned Personnel: ${report.assignedToName}',
                                              style: const TextStyle(
                                                fontWeight: FontWeight.w600,
                                                fontSize: 13,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                    if (report.resolutionNotes != null &&
                                        report.resolutionNotes!
                                            .trim()
                                            .isNotEmpty) ...[
                                      const SizedBox(
                                        height: AppTheme.spacingMd,
                                      ),
                                      Builder(
                                        builder: (ctx) {
                                          final isDark = Theme.of(ctx).brightness == Brightness.dark;
                                          return Container(
                                            padding: const EdgeInsets.all(
                                              AppTheme.spacingSm,
                                            ),
                                            decoration: BoxDecoration(
                                              color: isDark
                                                  ? AppTheme.statusResolvedBgDark
                                                  : AppTheme.statusResolvedBgLight,
                                              borderRadius: BorderRadius.circular(
                                                AppTheme.radiusSm,
                                              ),
                                              border: Border.all(
                                                color: isDark
                                                    ? AppTheme.statusResolvedTextDark.withValues(alpha: 0.3)
                                                    : const Color(0xFFA7F3D0),
                                              ),
                                            ),
                                            child: Column(
                                              crossAxisAlignment:
                                                  CrossAxisAlignment.start,
                                              children: [
                                                Text(
                                                  'Official Resolution Summary',
                                                  style: TextStyle(
                                                    fontWeight: FontWeight.w700,
                                                    color: isDark
                                                        ? AppTheme.statusResolvedTextDark
                                                        : AppTheme.statusResolvedTextLight,
                                                    fontSize: 13,
                                                  ),
                                                ),
                                                const SizedBox(height: 4),
                                                Text(
                                                  report.resolutionNotes!,
                                                  style: TextStyle(
                                                    fontSize: 13,
                                                    color: isDark
                                                        ? AppTheme.slate200
                                                        : const Color(0xFF065F46),
                                                  ),
                                                ),
                                              ],
                                            ),
                                          );
                                        },
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                            ),
                            const SizedBox(height: AppTheme.spacingMd),

                            // Status Progress Timeline Card
                            Card(
                              child: Padding(
                                padding:
                                    const EdgeInsets.all(AppTheme.spacingMd),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Status Timeline',
                                      style: Theme.of(context)
                                          .textTheme
                                          .titleMedium,
                                    ),
                                    const SizedBox(height: AppTheme.spacingMd),
                                    _LifecycleProgressStrip(
                                      currentStatus: report.status,
                                    ),
                                    const Divider(height: 28),
                                    if (report.statusHistories.isEmpty)
                                      Text(
                                        'Report has been logged and awaits initial verification.',
                                        style: Theme.of(context)
                                            .textTheme
                                            .bodySmall,
                                      )
                                    else
                                      ...report.statusHistories.map((history) {
                                        return Padding(
                                          padding: const EdgeInsets.only(
                                            bottom: 14,
                                          ),
                                          child: Row(
                                            crossAxisAlignment:
                                                CrossAxisAlignment.start,
                                            children: [
                                              Container(
                                                margin: const EdgeInsets.only(
                                                  top: 4,
                                                ),
                                                width: 10,
                                                height: 10,
                                                decoration: BoxDecoration(
                                                  color: _statusColor(
                                                    history.newStatus,
                                                  ),
                                                  shape: BoxShape.circle,
                                                ),
                                              ),
                                              const SizedBox(width: 12),
                                              Expanded(
                                                child: Column(
                                                  crossAxisAlignment:
                                                      CrossAxisAlignment.start,
                                                  children: [
                                                    Row(
                                                      mainAxisAlignment:
                                                          MainAxisAlignment
                                                              .spaceBetween,
                                                      children: [
                                                        Text(
                                                          history.statusLabel,
                                                          style:
                                                              const TextStyle(
                                                            fontWeight:
                                                                FontWeight.w700,
                                                            fontSize: 13.5,
                                                          ),
                                                        ),
                                                        if (history.createdAt !=
                                                            null)
                                                          Text(
                                                            dateFormat.format(
                                                              history.createdAt!
                                                                  .toLocal(),
                                                            ),
                                                            style: Theme.of(
                                                              context,
                                                            ).textTheme.bodySmall,
                                                          ),
                                                      ],
                                                    ),
                                                    if (history.notes != null &&
                                                        history.notes!
                                                            .trim()
                                                            .isNotEmpty) ...[
                                                      const SizedBox(height: 2),
                                                      Text(
                                                        history.notes!,
                                                        style: Theme.of(context)
                                                            .textTheme
                                                            .bodyMedium,
                                                      ),
                                                    ],
                                                    const SizedBox(height: 2),
                                                    Text(
                                                      'Updated by ${history.changedByName}',
                                                      style: Theme.of(context)
                                                          .textTheme
                                                          .bodySmall,
                                                    ),
                                                  ],
                                                ),
                                              ),
                                            ],
                                          ),
                                        );
                                      }),
                                  ],
                                ),
                              ),
                            ),
                            const SizedBox(height: AppTheme.spacingMd),

                            // Attached Photos
                            if (report.images.isNotEmpty) ...[
                              Card(
                                child: Padding(
                                  padding:
                                      const EdgeInsets.all(AppTheme.spacingMd),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Attached Photo Evidence',
                                        style: Theme.of(context)
                                            .textTheme
                                            .titleMedium,
                                      ),
                                      const SizedBox(
                                        height: AppTheme.spacingSm,
                                      ),
                                      ...report.images.map((img) {
                                        return Padding(
                                          padding: const EdgeInsets.only(
                                            bottom: 12,
                                          ),
                                          child: ReportPhoto.detail(
                                            image: img,
                                            maxHeight: 320,
                                          ),
                                        );
                                      }),
                                    ],
                                  ),
                                ),
                              ),
                              const SizedBox(height: AppTheme.spacingMd),
                            ],

                            // Location & Map Card
                            Card(
                              child: Padding(
                                padding:
                                    const EdgeInsets.all(AppTheme.spacingMd),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Incident Location',
                                      style: Theme.of(context)
                                          .textTheme
                                          .titleMedium,
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      report.addressText,
                                      style: Theme.of(context)
                                          .textTheme
                                          .bodyMedium,
                                    ),
                                    if (report.landmark != null &&
                                        report.landmark!.isNotEmpty)
                                      Text(
                                        'Landmark: ${report.landmark}',
                                        style: Theme.of(context)
                                            .textTheme
                                            .bodySmall,
                                      ),
                                    if (report.latitude != null &&
                                        report.longitude != null) ...[
                                      const SizedBox(
                                        height: AppTheme.spacingSm,
                                      ),
                                      ClipRRect(
                                        borderRadius: BorderRadius.circular(
                                          AppTheme.radiusSm,
                                        ),
                                        child: SizedBox(
                                          height: 180,
                                          child: FlutterMap(
                                            options: MapOptions(
                                              initialCenter: LatLng(
                                                report.latitude!,
                                                report.longitude!,
                                              ),
                                              initialZoom: 15.5,
                                            ),
                                            children: [
                                              TileLayer(
                                                urlTemplate:
                                                    'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                                                userAgentPackageName:
                                                    'ph.gov.barangay.community_report_app',
                                                tileBuilder:
                                                    (context, tileWidget, tile) {
                                                  return Container(
                                                    color: const Color(0xFFE2E8F0),
                                                    child: tileWidget,
                                                  );
                                                },
                                              ),
                                              const RichAttributionWidget(
                                                attributions: [
                                                  TextSourceAttribution(
                                                    'OpenStreetMap contributors',
                                                  ),
                                                ],
                                              ),
                                              MarkerLayer(
                                                markers: [
                                                  Marker(
                                                    point: LatLng(
                                                      report.latitude!,
                                                      report.longitude!,
                                                    ),
                                                    width: 40,
                                                    height: 40,
                                                    child: const Icon(
                                                      Icons.location_on,
                                                      color:
                                                          AppTheme.dangerColor,
                                                      size: 36,
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                            ),

                            // Official Barangay Updates / Public Comments
                            if (report.publicComments.isNotEmpty) ...[
                              const SizedBox(height: AppTheme.spacingMd),
                              Card(
                                child: Padding(
                                  padding:
                                      const EdgeInsets.all(AppTheme.spacingMd),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Barangay Remarks',
                                        style: Theme.of(context)
                                            .textTheme
                                            .titleMedium,
                                      ),
                                      const SizedBox(
                                        height: AppTheme.spacingSm,
                                      ),
                                      ...report.publicComments.map((comment) {
                                        final isDark = Theme.of(context).brightness == Brightness.dark;
                                        return Container(
                                          margin: const EdgeInsets.only(
                                            bottom: 8,
                                          ),
                                          padding: const EdgeInsets.all(10),
                                          decoration: BoxDecoration(
                                            color: isDark ? AppTheme.slate800 : AppTheme.slate100,
                                            borderRadius: BorderRadius.circular(
                                              AppTheme.radiusSm,
                                            ),
                                            border: Border.all(
                                              color: isDark ? AppTheme.slate700 : AppTheme.slate200,
                                            ),
                                          ),
                                          child: Column(
                                            crossAxisAlignment:
                                                CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                comment.authorName,
                                                style: TextStyle(
                                                  fontWeight: FontWeight.w600,
                                                  fontSize: 12.5,
                                                  color: isDark ? AppTheme.slate100 : AppTheme.slate900,
                                                ),
                                              ),
                                              const SizedBox(height: 4),
                                              Text(
                                                comment.body,
                                                style: TextStyle(
                                                  fontSize: 13,
                                                  color: isDark ? AppTheme.slate200 : AppTheme.slate800,
                                                ),
                                              ),
                                            ],
                                          ),
                                        );
                                      }),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    ),
                  );
                },
              ),
            );
          },
        ),
      ),
    );
  }
}

class _LifecycleProgressStrip extends StatelessWidget {
  final String currentStatus;

  const _LifecycleProgressStrip({required this.currentStatus});

  static const _steps = [
    ('submitted', 'Submitted'),
    ('verified', 'Verified'),
    ('assigned', 'Assigned'),
    ('in_progress', 'In Progress'),
    ('resolved', 'Resolved'),
  ];

  int _activeIndex() {
    for (var i = 0; i < _steps.length; i++) {
      if (_steps[i].$1 == currentStatus) {
        return i;
      }
    }
    return 0;
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    if (currentStatus == 'rejected' || currentStatus == 'duplicate') {
      return Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: isDark ? AppTheme.statusHazardBgDark : const Color(0xFFFEF2F2),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: isDark ? AppTheme.statusHazardTextDark.withValues(alpha: 0.3) : const Color(0xFFFECACA),
          ),
        ),
        child: Text(
          'This report was marked as ${currentStatus.toUpperCase()}.',
          style: TextStyle(
            color: isDark ? AppTheme.statusHazardTextDark : AppTheme.dangerColor,
            fontWeight: FontWeight.w600,
            fontSize: 13,
          ),
        ),
      );
    }

    final activeIdx = _activeIndex();

    return Row(
      children: List.generate(_steps.length, (index) {
        final isCompleted = index <= activeIdx;
        final label = _steps[index].$2;

        return Expanded(
          child: Column(
            children: [
              Container(
                height: 6,
                margin: const EdgeInsets.symmetric(horizontal: 2),
                decoration: BoxDecoration(
                  color: isCompleted
                      ? (isDark ? const Color(0xFF14B8A6) : AppTheme.primaryColor)
                      : (isDark ? AppTheme.slate800 : AppTheme.borderColor),
                  borderRadius: BorderRadius.circular(3),
                ),
              ),
              const SizedBox(height: 6),
              FittedBox(
                fit: BoxFit.scaleDown,
                child: Text(
                  label,
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  style: TextStyle(
                    fontSize: 10.5,
                    fontWeight:
                        isCompleted ? FontWeight.w700 : FontWeight.w500,
                    color: isCompleted
                        ? (isDark ? AppTheme.slate50 : AppTheme.textPrimary)
                        : (isDark ? AppTheme.slate400 : AppTheme.textSecondary),
                  ),
                ),
              ),
            ],
          ),
        );
      }),
    );
  }
}
