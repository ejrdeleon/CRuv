class CategoryModel {
  final int id;
  final String name;
  final String slug;
  final String? description;
  final String? icon;
  final String defaultPriority;
  final bool isHazardous;
  final String? safetyAdvisory;
  final String? departmentName;

  const CategoryModel({
    required this.id,
    required this.name,
    required this.slug,
    this.description,
    this.icon,
    this.defaultPriority = 'medium',
    this.isHazardous = false,
    this.safetyAdvisory,
    this.departmentName,
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String? ?? '',
      slug: json['slug'] as String? ?? '',
      description: json['description'] as String?,
      icon: json['icon'] as String?,
      defaultPriority: json['default_priority'] as String? ?? 'medium',
      isHazardous: json['is_hazardous'] as bool? ?? false,
      safetyAdvisory: json['safety_advisory'] as String?,
      departmentName: json['department_name'] as String?,
    );
  }
}

class ReportImageModel {
  final int id;
  final String url;
  final String? thumbnailUrl;
  final int? width;
  final int? height;
  final String? caption;
  final String type;

  const ReportImageModel({
    required this.id,
    required this.url,
    this.thumbnailUrl,
    this.width,
    this.height,
    this.caption,
    this.type = 'evidence',
  });

  factory ReportImageModel.fromJson(Map<String, dynamic> json) {
    return ReportImageModel(
      id: (json['id'] as num).toInt(),
      url: json['url'] as String? ?? '',
      thumbnailUrl: json['thumbnail_url'] as String?,
      width: (json['width'] as num?)?.toInt(),
      height: (json['height'] as num?)?.toInt(),
      caption: json['caption'] as String?,
      type: json['type'] as String? ?? 'evidence',
    );
  }
}

class StatusHistoryModel {
  final int id;
  final String? oldStatus;
  final String newStatus;
  final String statusLabel;
  final String? notes;
  final String changedByName;
  final DateTime? createdAt;

  const StatusHistoryModel({
    required this.id,
    this.oldStatus,
    required this.newStatus,
    required this.statusLabel,
    this.notes,
    required this.changedByName,
    this.createdAt,
  });

  factory StatusHistoryModel.fromJson(Map<String, dynamic> json) {
    return StatusHistoryModel(
      id: (json['id'] as num).toInt(),
      oldStatus: json['old_status'] as String?,
      newStatus: json['new_status'] as String? ?? 'submitted',
      statusLabel: json['status_label'] as String? ?? 'Submitted',
      notes: json['notes'] as String?,
      changedByName: json['changed_by_name'] as String? ?? 'System',
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString())
          : null,
    );
  }
}

class CommentModel {
  final int id;
  final String body;
  final String authorName;
  final String authorRole;
  final DateTime? createdAt;

  const CommentModel({
    required this.id,
    required this.body,
    required this.authorName,
    required this.authorRole,
    this.createdAt,
  });

  factory CommentModel.fromJson(Map<String, dynamic> json) {
    return CommentModel(
      id: (json['id'] as num).toInt(),
      body: json['body'] as String? ?? '',
      authorName: json['author_name'] as String? ?? 'Barangay Office',
      authorRole: json['author_role'] as String? ?? 'admin',
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString())
          : null,
    );
  }
}

class ReportModel {
  final int id;
  final String referenceNumber;
  final String title;
  final String description;
  final String status;
  final String statusLabel;
  final String priority;
  final double? latitude;
  final double? longitude;
  final String addressText;
  final String? barangay;
  final String? landmark;
  final bool isHazardWarningAcknowledged;
  final String? resolutionNotes;
  final String? rejectionReason;
  final CategoryModel? category;
  final String? assignedToName;
  final String? assignedUnitName;
  final List<ReportImageModel> images;
  final List<StatusHistoryModel> statusHistories;
  final List<CommentModel> publicComments;
  final DateTime? createdAt;
  final DateTime? updatedAt;
  final DateTime? resolvedAt;

  const ReportModel({
    required this.id,
    required this.referenceNumber,
    required this.title,
    required this.description,
    required this.status,
    required this.statusLabel,
    required this.priority,
    this.latitude,
    this.longitude,
    required this.addressText,
    this.barangay,
    this.landmark,
    this.isHazardWarningAcknowledged = false,
    this.resolutionNotes,
    this.rejectionReason,
    this.category,
    this.assignedToName,
    this.assignedUnitName,
    this.images = const [],
    this.statusHistories = const [],
    this.publicComments = const [],
    this.createdAt,
    this.updatedAt,
    this.resolvedAt,
  });

  factory ReportModel.fromJson(Map<String, dynamic> json) {
    final imagesJson = json['images'] as List<dynamic>? ?? [];
    final historiesJson = json['status_histories'] as List<dynamic>? ?? [];
    final commentsJson = json['public_comments'] as List<dynamic>? ?? [];

    return ReportModel(
      id: (json['id'] as num).toInt(),
      referenceNumber: json['reference_number'] as String? ?? '',
      title: json['title'] as String? ?? '',
      description: json['description'] as String? ?? '',
      status: json['status'] as String? ?? 'submitted',
      statusLabel: json['status_label'] as String? ?? 'Submitted',
      priority: json['priority'] as String? ?? 'medium',
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
      addressText: json['address_text'] as String? ?? '',
      barangay: json['barangay'] as String?,
      landmark: json['landmark'] as String?,
      isHazardWarningAcknowledged:
          json['is_hazard_warning_acknowledged'] as bool? ?? false,
      resolutionNotes: json['resolution_notes'] as String?,
      rejectionReason: json['rejection_reason'] as String?,
      category: json['category'] != null
          ? CategoryModel.fromJson(json['category'] as Map<String, dynamic>)
          : null,
      assignedToName: (json['assigned_to_name'] ??
          json['assigned_staff_name']) as String?,
      assignedUnitName: (json['assigned_unit_name'] ??
          json['department_name']) as String?,
      images: imagesJson
          .map((e) => ReportImageModel.fromJson(e as Map<String, dynamic>))
          .toList(),
      statusHistories: historiesJson
          .map((e) => StatusHistoryModel.fromJson(e as Map<String, dynamic>))
          .toList(),
      publicComments: commentsJson
          .map((e) => CommentModel.fromJson(e as Map<String, dynamic>))
          .toList(),
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString())
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.tryParse(json['updated_at'].toString())
          : null,
      resolvedAt: json['resolved_at'] != null
          ? DateTime.tryParse(json['resolved_at'].toString())
          : null,
    );
  }
}

class CommunityPinModel {
  final int id;
  final String referenceNumber;
  final String title;
  final String categoryName;
  final String status;
  final String statusLabel;
  final String priority;
  final double latitude;
  final double longitude;
  final String addressText;
  final bool isHazardous;

  const CommunityPinModel({
    required this.id,
    required this.referenceNumber,
    required this.title,
    required this.categoryName,
    required this.status,
    required this.statusLabel,
    required this.priority,
    required this.latitude,
    required this.longitude,
    required this.addressText,
    this.isHazardous = false,
  });

  factory CommunityPinModel.fromJson(Map<String, dynamic> json) {
    return CommunityPinModel(
      id: (json['id'] as num).toInt(),
      referenceNumber: json['reference_number'] as String? ?? '',
      title: json['title'] as String? ?? '',
      categoryName: json['category_name'] as String? ?? 'Community Issue',
      status: json['status'] as String? ?? 'submitted',
      statusLabel: json['status_label'] as String? ?? 'Submitted',
      priority: json['priority'] as String? ?? 'medium',
      latitude: (json['latitude'] as num).toDouble(),
      longitude: (json['longitude'] as num).toDouble(),
      addressText: json['address_text'] as String? ?? '',
      isHazardous: json['is_hazardous'] as bool? ?? false,
    );
  }
}
