import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_theme.dart';
import '../domain/report_models.dart';
import 'report_detail_screen.dart';
import 'report_form_screen.dart';
import 'report_provider.dart';

class MyReportsScreen extends StatefulWidget {
  const MyReportsScreen({super.key});

  @override
  State<MyReportsScreen> createState() => _MyReportsScreenState();
}

class _MyReportsScreenState extends State<MyReportsScreen> {
  final _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    final provider = context.read<ReportProvider>();
    _searchController.text = provider.searchQuery;
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _applyFilter(String status) {
    context.read<ReportProvider>().loadMyReports(
          status: status,
          search: _searchController.text,
        );
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ReportProvider>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Submitted Reports'),
        actions: [
          IconButton(
            tooltip: 'Refresh Reports',
            onPressed: () => provider.loadMyReports(),
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Search & Filter Toolbar
            Container(
              color: Theme.of(context).cardTheme.color,
              padding: const EdgeInsets.fromLTRB(
                AppTheme.spacingMd,
                AppTheme.spacingSm,
                AppTheme.spacingMd,
                AppTheme.spacingSm,
              ),
              child: Column(
                children: [
                  TextField(
                    controller: _searchController,
                    textInputAction: TextInputAction.search,
                    onSubmitted: (value) {
                      provider.loadMyReports(search: value);
                    },
                    decoration: InputDecoration(
                      hintText: 'Search reference # or title...',
                      prefixIcon: const Icon(Icons.search),
                      suffixIcon: _searchController.text.isNotEmpty
                          ? IconButton(
                              onPressed: () {
                                _searchController.clear();
                                provider.loadMyReports(search: '');
                                setState(() {});
                              },
                              icon: const Icon(Icons.clear),
                            )
                          : null,
                    ),
                    onChanged: (_) => setState(() {}),
                  ),
                  const SizedBox(height: AppTheme.spacingSm),
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: [
                        _FilterChipButton(
                          label: 'All (${provider.stats.total})',
                          selected: provider.statusFilter == 'all',
                          onTap: () => _applyFilter('all'),
                        ),
                        const SizedBox(width: 8),
                        _FilterChipButton(
                          label: 'Active (${provider.stats.active})',
                          selected: provider.statusFilter == 'active',
                          onTap: () => _applyFilter('active'),
                        ),
                        const SizedBox(width: 8),
                        _FilterChipButton(
                          label: 'In Progress',
                          selected: provider.statusFilter == 'in_progress',
                          onTap: () => _applyFilter('in_progress'),
                        ),
                        const SizedBox(width: 8),
                        _FilterChipButton(
                          label: 'Resolved (${provider.stats.resolved})',
                          selected: provider.statusFilter == 'resolved',
                          onTap: () => _applyFilter('resolved'),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const Divider(height: 1),

            // Reports List / States
            Expanded(
              child: Builder(
                builder: (context) {
                  if (provider.isLoadingList && provider.reports.isEmpty) {
                    return const Center(child: CircularProgressIndicator());
                  }

                  if (provider.errorMessage != null &&
                      provider.reports.isEmpty) {
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
                              onPressed: () => provider.loadMyReports(),
                              icon: const Icon(Icons.refresh),
                              label: const Text('Retry Connection'),
                            ),
                          ],
                        ),
                      ),
                    );
                  }

                  if (provider.reports.isEmpty) {
                    return Center(
                      child: Padding(
                        padding: const EdgeInsets.all(AppTheme.spacingLg),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(
                              Icons.assignment_outlined,
                              size: 48,
                              color: AppTheme.textSecondary,
                            ),
                            const SizedBox(height: AppTheme.spacingMd),
                            Text(
                              'No reports found',
                              style: Theme.of(context).textTheme.titleMedium,
                            ),
                            const SizedBox(height: 6),
                            Text(
                              'You have not submitted any reports matching this filter yet.',
                              textAlign: TextAlign.center,
                              style: Theme.of(context).textTheme.bodySmall,
                            ),
                            const SizedBox(height: AppTheme.spacingMd),
                            ElevatedButton.icon(
                              onPressed: () =>
                                  showReportIssueBottomSheet(context),
                              icon: const Icon(Icons.add_a_photo_outlined),
                              label: const Text('Report an Issue'),
                            ),
                          ],
                        ),
                      ),
                    );
                  }

                  return RefreshIndicator(
                    onRefresh: () => provider.loadMyReports(),
                    child: LayoutBuilder(
                      builder: (context, constraints) {
                        final isTablet = constraints.maxWidth >= 720;

                        if (isTablet) {
                          final textScale =
                              MediaQuery.textScalerOf(context).scale(1.0);
                          final cardExtent =
                              (160.0 * textScale).clamp(156.0, 240.0);

                          return GridView.builder(
                            padding: const EdgeInsets.all(AppTheme.spacingMd),
                            gridDelegate:
                                SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 2,
                              crossAxisSpacing: 12,
                              mainAxisSpacing: 12,
                              mainAxisExtent: cardExtent,
                            ),
                            itemCount: provider.reports.length,
                            itemBuilder: (context, index) {
                              return ReportCardItem(
                                report: provider.reports[index],
                              );
                            },
                          );
                        }

                        return ListView.separated(
                          padding: const EdgeInsets.all(AppTheme.spacingMd),
                          itemCount: provider.reports.length,
                          separatorBuilder: (_, _) =>
                              const SizedBox(height: 10),
                          itemBuilder: (context, index) {
                            return ReportCardItem(
                              report: provider.reports[index],
                            );
                          },
                        );
                      },
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _FilterChipButton extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _FilterChipButton({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return ChoiceChip(
      label: Text(label),
      selected: selected,
      onSelected: (_) => onTap(),
      selectedColor: theme.colorScheme.primary.withValues(alpha: isDark ? 0.25 : 0.14),
      labelStyle: TextStyle(
        fontFamily: AppTheme.fontFamily,
        color: selected
            ? theme.colorScheme.primary
            : (isDark ? AppTheme.slate300 : AppTheme.slate700),
        fontWeight: selected ? FontWeight.w700 : FontWeight.w500,
      ),
    );
  }
}

class ReportCardItem extends StatelessWidget {
  final ReportModel report;

  const ReportCardItem({super.key, required this.report});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final dateStr = report.createdAt != null
        ? DateFormat('MMM d, yyyy').format(report.createdAt!.toLocal())
        : '';

    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(AppTheme.radiusBento),
        onTap: () {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => ReportDetailScreen(reportId: report.id),
            ),
          );
        },
        child: Padding(
          padding: const EdgeInsets.all(AppTheme.spacingMd),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          report.referenceNumber,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontFamily: AppTheme.fontFamily,
                            fontWeight: FontWeight.w700,
                            fontSize: 12.5,
                            color: theme.colorScheme.primary,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      StatusPill(
                        status: report.status,
                        customLabel: report.statusLabel,
                        priority: report.priority,
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    report.title,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: theme.textTheme.titleMedium,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    report.addressText,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: theme.textTheme.bodySmall,
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      report.category?.name ?? 'Community Issue',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: theme.textTheme.bodySmall?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                  Text(
                    dateStr,
                    style: theme.textTheme.bodySmall,
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
