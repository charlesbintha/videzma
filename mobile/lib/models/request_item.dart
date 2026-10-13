class RequestItem {
  final String category;
  final String title;
  final String time;
  final RequestStatus status;
  final String reference;

  const RequestItem({
    required this.category,
    required this.title,
    required this.time,
    required this.status,
    required this.reference,
  });
}

enum RequestStatus { done, inProgress, pending }
