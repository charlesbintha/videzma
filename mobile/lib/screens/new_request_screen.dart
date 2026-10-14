import '../services/location_service.dart';
import "package:flutter/material.dart";
import '../widgets/app_map.dart';
import "../theme/app_colors.dart";
import "../widgets/primary_button.dart";
import "../services/service_request_service.dart";
import "../services/payment_service.dart";
import "../services/location_store.dart";

import "../models/available_driver.dart";
import "payment_webview_screen.dart";

class NewRequestScreen extends StatefulWidget {
  const NewRequestScreen({super.key});

  @override
  State<NewRequestScreen> createState() => _NewRequestScreenState();
}

class _NewRequestScreenState extends State<NewRequestScreen> {
  final addressController = TextEditingController();
  final notesController = TextEditingController();
  String? selectedFosseType;
  double selectedVolume = 5.0;
  String? selectedUrgency;
  AvailableDriver? selectedDriver;
  String? selectedPaymentMethod;
  int step = 0;
  LatLng? site;
  bool locating = false;
  final scrollController = ScrollController();
  bool isLoading = false;
  bool isLoadingDrivers = false;
  String? errorMessage;
  int? estimatedPrice;
  final service = ServiceRequestService();
  final paymentService = PaymentService();
  List<AvailableDriver> drivers = [];

  final fosseTypes = const [
    {"value": "septique", "label": "Fosse septique"},
    {"value": "traditionnelle", "label": "Fosse traditionnelle"},
    {"value": "toutes_eaux", "label": "Fosse toutes eaux"},
  ];

  final urgencyLevels = const [
    {"value": "normal", "label": "Normal", "icon": Icons.schedule},
    {"value": "urgent", "label": "Urgent", "icon": Icons.priority_high},
    {"value": "emergency", "label": "Urgence", "icon": Icons.warning},
  ];

  final paymentMethods = const [
    {
      "value": "orange_money",
      "label": "Orange Money",
      "icon": Icons.phone_android
    },
    {"value": "wave", "label": "Wave", "icon": Icons.waves},
    {"value": "cash", "label": "Espèces", "icon": Icons.money},
  ];

  @override
  void initState() {
    super.initState();
    selectedFosseType = "septique";
    selectedUrgency = "normal";
    if (LocationStore.hasLocation)
      site = LatLng(LocationStore.latitude!, LocationStore.longitude!);
    _updatePriceEstimate();
  }

  @override
  void dispose() {
    scrollController.dispose();
    addressController.dispose();
    notesController.dispose();
    super.dispose();
  }

  Future<void> _loadDrivers() async {
    if (site == null) {
      setState(() {
        errorMessage = "Position non disponible. Activez la localisation.";
      });
      return;
    }

    setState(() {
      isLoadingDrivers = true;
      errorMessage = null;
    });

    final lat = site!.latitude;
    final lng = site!.longitude;

    final result = await service.getAvailableDrivers(
      latitude: lat,
      longitude: lng,
    );

    if (!mounted) return;

    setState(() {
      drivers = result;
      isLoadingDrivers = false;
      if (drivers.isEmpty) {
        errorMessage = "Aucun vidangeur disponible pour le moment.";
      }
    });
  }

  Future<void> _updatePriceEstimate() async {
    final volume = selectedVolume;
    final result = await service.estimatePrice(estimatedVolume: volume);
    if (result != null && mounted && volume == selectedVolume) {
      setState(() {
        estimatedPrice = result['price_amount'] as int?;
      });
    }
  }

  Future<void> _submit() async {
    if (addressController.text.trim().isEmpty) {
      setState(() {
        errorMessage = "Entrez votre adresse.";
      });
      return;
    }

    if (selectedPaymentMethod == null) {
      setState(() {
        errorMessage = "Choisissez un moyen de paiement.";
      });
      return;
    }

    if (site == null) {
      setState(() {
        errorMessage = "Position non disponible.";
      });
      return;
    }

    setState(() {
      isLoading = true;
      errorMessage = null;
    });

    final lat = site!.latitude;
    final lng = site!.longitude;

    final result = await service.create(
      address: addressController.text.trim(),
      fosseType: selectedFosseType,
      estimatedVolume: selectedVolume,
      urgencyLevel: selectedUrgency,
      clientNotes: notesController.text.trim().isEmpty
          ? null
          : notesController.text.trim(),
      driverId: selectedDriver?.id,
      paymentMethod: selectedPaymentMethod!,
      latitude: lat,
      longitude: lng,
    );

    if (!mounted) return;

    if (result == null) {
      setState(() {
        isLoading = false;
        errorMessage = "Erreur lors de l'envoi.";
      });
      return;
    }

    final serviceRequestId = result['id'] as int?;
    final paymentMethod =
        result['payment_method'] as String? ?? selectedPaymentMethod;
    final priceFormatted = result['price_formatted'] ?? '';

    // Paiement en ligne (Orange Money ou Wave) → rediriger vers Paytech
    if (serviceRequestId != null &&
        (paymentMethod == 'orange_money' || paymentMethod == 'wave')) {
      final redirectUrl =
          await paymentService.initiateServicePayment(serviceRequestId);

      if (!mounted) return;
      setState(() => isLoading = false);

      if (redirectUrl == null) {
        // Paytech indisponible : la demande est créée, le paiement se fera plus tard
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
                "Demande créée. Le paiement en ligne est temporairement indisponible."),
            backgroundColor: AppColors.warning,
          ),
        );
        Navigator.of(context).pop();
        return;
      }

      final paymentResult = await Navigator.of(context).push<PaymentResult>(
        MaterialPageRoute(
          builder: (_) => PaymentWebViewScreen(
            redirectUrl: redirectUrl,
            title: "Payer $priceFormatted",
          ),
        ),
      );

      if (!mounted) return;

      if (paymentResult == PaymentResult.success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text("Paiement effectué avec succès !"),
            backgroundColor: AppColors.success,
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
                "Paiement annulé. Votre demande est enregistrée, vous pourrez payer plus tard."),
            backgroundColor: AppColors.warning,
            duration: Duration(seconds: 4),
          ),
        );
      }

      Navigator.of(context).pop();
      return;
    }

    // Paiement en espèces
    setState(() => isLoading = false);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text("Demande envoyée ! Montant estimé: $priceFormatted"),
        backgroundColor: AppColors.success,
      ),
    );

    Navigator.of(context).pop();
  }

  void _next() {
    if (step == 0 && (addressController.text.trim().isEmpty || site == null)) {
      setState(() => errorMessage =
          'Renseignez une adresse et sélectionnez le lieu sur la carte.');
      return;
    }
    if (step < 2) {
      setState(() {
        step++;
        errorMessage = null;
      });
      scrollController.jumpTo(0);
      if (step == 2) _loadDrivers();
    } else {
      _submit();
    }
  }

  Future<void> _locate() async {
    setState(() {
      locating = true;
      errorMessage = null;
    });
    final locationService = LocationService();
    final ok = await locationService.captureAndSend();
    if (!mounted) return;
    setState(() {
      locating = false;
      if (ok)
        site = LatLng(LocationStore.latitude!, LocationStore.longitude!);
      else
        errorMessage = locationService.lastError ??
            'GPS indisponible. Choisissez le lieu sur la carte.';
    });
  }

  String get _price => estimatedPrice == null
      ? 'À estimer'
      : '${estimatedPrice!.toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]} ')} FCFA';

  @override
  Widget build(BuildContext context) {
    const titles = [
      'Où intervenons-nous ?',
      'Quel est votre besoin ?',
      'Tout est prêt ?'
    ];
    const captions = [
      'Choisissez le lieu exact de la vidange.',
      'Quelques détails pour préparer l’intervention.',
      'Vérifiez votre demande avant de l’envoyer.'
    ];
    return PopScope(
        canPop: !isLoading,
        child: Scaffold(
          backgroundColor: const Color(0xFFF5F7FB),
          appBar: AppBar(
            leading: IconButton(
                tooltip: 'Retour',
                onPressed: isLoading
                    ? null
                    : () {
                        if (step > 0) {
                          setState(() {
                            step--;
                            errorMessage = null;
                          });
                          scrollController.jumpTo(0);
                        } else
                          Navigator.of(context).pop();
                      },
                icon: const Icon(Icons.arrow_back)),
            title: const Text('Nouvelle intervention',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600)),
            actions: [
              Padding(
                  padding: const EdgeInsets.only(right: 20),
                  child: Center(
                      child: Text('${step + 1} / 3',
                          style: const TextStyle(
                              color: AppColors.primary,
                              fontWeight: FontWeight.w700))))
            ],
          ),
          body: AbsorbPointer(
              absorbing: isLoading,
              child: Column(children: [
                Padding(
                    padding: const EdgeInsets.fromLTRB(20, 8, 20, 20),
                    child: Row(
                        children: List.generate(
                            3,
                            (index) => Expanded(
                                    child: Column(children: [
                                  Container(
                                      height: 4,
                                      margin: const EdgeInsets.symmetric(
                                          horizontal: 3),
                                      decoration: BoxDecoration(
                                          color: index <= step
                                              ? AppColors.primary
                                              : const Color(0xFFDFE5F0),
                                          borderRadius:
                                              BorderRadius.circular(3))),
                                  const SizedBox(height: 8),
                                  Text(
                                      ['Lieu', 'Besoin', 'Confirmation'][index],
                                      style: TextStyle(
                                          fontSize: 10,
                                          color: index == step
                                              ? AppColors.primary
                                              : AppColors.textMuted,
                                          fontWeight: FontWeight.w600)),
                                ]))))),
                Expanded(
                    child: ListView(
                        controller: scrollController,
                        padding: const EdgeInsets.fromLTRB(20, 4, 20, 24),
                        children: [
                      Text(titles[step],
                          style: const TextStyle(
                              fontSize: 27,
                              fontWeight: FontWeight.w800,
                              letterSpacing: -.6,
                              height: 1.2)),
                      const SizedBox(height: 10),
                      Text(captions[step],
                          style: const TextStyle(
                              fontSize: 13,
                              color: AppColors.textMuted,
                              height: 1.5)),
                      const SizedBox(height: 24),
                      if (step == 0) ...[
                        // Adresse
                        _FormCard(
                          child: TextField(
                            controller: addressController,
                            decoration: const InputDecoration(
                              labelText: "Adresse",
                              hintText: "Ex: Dakar, Médina, Rue 10...",
                              prefixIcon: Icon(Icons.location_on_outlined),
                              border: InputBorder.none,
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),

                        Container(
                            height: 260,
                            clipBehavior: Clip.antiAlias,
                            decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(22)),
                            child: AppMap(
                              key: ValueKey(locating),
                              initialCameraPosition: MapCameraPosition(
                                  target:
                                      site ?? const LatLng(14.6937, -17.4441),
                                  zoom: 13),
                              onTap: (point) => setState(() {
                                site = point;
                                selectedDriver = null;
                                errorMessage = null;
                              }),
                              markers: {
                                if (site != null)
                                  MapMarker(
                                      id: 'intervention',
                                      position: site!,
                                      color: Colors.blue)
                              },
                              zoomControlsEnabled: true,
                            )),
                        const SizedBox(height: 12),
                        Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Icon(
                                  site == null
                                      ? Icons.touch_app_outlined
                                      : Icons.check_circle_outline,
                                  size: 18,
                                  color: AppColors.primary),
                              const SizedBox(width: 8),
                              Expanded(
                                  child: Text(
                                      site == null
                                          ? 'Touchez la carte pour placer le lieu d’intervention.'
                                          : 'Lieu sélectionné. Touchez la carte pour le déplacer.',
                                      style: const TextStyle(
                                          fontSize: 12,
                                          color: AppColors.textMuted,
                                          height: 1.5))),
                            ]),
                        const SizedBox(height: 16),
                        OutlinedButton.icon(
                            onPressed: locating ? null : _locate,
                            icon: const Icon(Icons.my_location, size: 18),
                            label: Text(locating
                                ? 'Recherche du GPS…'
                                : 'Utiliser ma position GPS')),
                      ],
                      if (step == 1) ...[
                        // Type de fosse
                        _FormCard(
                          child: DropdownButtonFormField<String>(
                            value: selectedFosseType,
                            items: fosseTypes
                                .map(
                                  (type) => DropdownMenuItem(
                                    value: type["value"] as String,
                                    child: Text(type["label"] as String),
                                  ),
                                )
                                .toList(),
                            onChanged: (value) {
                              setState(() {
                                selectedFosseType = value;
                              });
                            },
                            decoration: const InputDecoration(
                              labelText: "Type de fosse",
                              prefixIcon: Icon(Icons.water_damage_outlined),
                              border: InputBorder.none,
                            ),
                            icon: const Icon(Icons.keyboard_arrow_down_rounded),
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Volume estimé
                        _FormCard(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text(
                                    "Volume estimé",
                                    style: TextStyle(
                                        fontSize: 14,
                                        color: AppColors.textMuted),
                                  ),
                                  Text(
                                    "${selectedVolume.toInt()} m³",
                                    style: const TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.w700,
                                      color: AppColors.primary,
                                    ),
                                  ),
                                ],
                              ),
                              Slider(
                                value: selectedVolume,
                                min: 1,
                                max: 20,
                                divisions: 19,
                                activeColor: AppColors.primary,
                                inactiveColor:
                                    AppColors.primary.withOpacity(0.2),
                                onChangeEnd: (_) => _updatePriceEstimate(),
                                onChanged: (value) {
                                  setState(() {
                                    selectedVolume = value;
                                    estimatedPrice = null;
                                  });
                                },
                              ),
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: const [
                                  Text("1 m³",
                                      style: TextStyle(
                                          fontSize: 12,
                                          color: AppColors.textMuted)),
                                  Text("20 m³",
                                      style: TextStyle(
                                          fontSize: 12,
                                          color: AppColors.textMuted)),
                                ],
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Niveau d'urgence
                        const Text(
                          "Niveau d'urgence",
                          style: TextStyle(
                              fontSize: 16, fontWeight: FontWeight.w600),
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: urgencyLevels.map((level) {
                            final value = level["value"] as String;
                            final label = level["label"] as String;
                            final icon = level["icon"] as IconData;
                            final isSelected = selectedUrgency == value;

                            Color bgColor = Colors.white;
                            Color borderColor = Colors.grey.shade300;
                            if (isSelected) {
                              if (value == "emergency") {
                                bgColor = AppColors.danger;
                                borderColor = AppColors.danger;
                              } else if (value == "urgent") {
                                bgColor = AppColors.warning;
                                borderColor = AppColors.warning;
                              } else {
                                bgColor = AppColors.primary;
                                borderColor = AppColors.primary;
                              }
                            }

                            return Expanded(
                              child: Padding(
                                padding:
                                    const EdgeInsets.symmetric(horizontal: 4),
                                child: GestureDetector(
                                  onTap: () {
                                    setState(() {
                                      selectedUrgency = value;
                                    });
                                  },
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(
                                        vertical: 14),
                                    decoration: BoxDecoration(
                                      color: bgColor,
                                      borderRadius: BorderRadius.circular(14),
                                      border: Border.all(
                                          color: borderColor,
                                          width: isSelected ? 2 : 1),
                                    ),
                                    child: Column(
                                      children: [
                                        Icon(
                                          icon,
                                          size: 24,
                                          color: isSelected
                                              ? Colors.white
                                              : AppColors.textMuted,
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          label,
                                          style: TextStyle(
                                            fontSize: 12,
                                            fontWeight: FontWeight.w600,
                                            color: isSelected
                                                ? Colors.white
                                                : AppColors.text,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                        const SizedBox(height: 16),

                        // Notes
                        _FormCard(
                          child: TextField(
                            controller: notesController,
                            maxLines: 2,
                            decoration: const InputDecoration(
                              labelText: "Notes (optionnel)",
                              hintText: "Informations complémentaires...",
                              prefixIcon: Icon(Icons.note_outlined),
                              border: InputBorder.none,
                            ),
                          ),
                        ),
                        const SizedBox(height: 20),
                      ],
                      if (step == 2) ...[
                        _FormCard(
                            child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                              const Text('VOTRE INTERVENTION',
                                  style: TextStyle(
                                      fontSize: 10,
                                      color: AppColors.primary,
                                      letterSpacing: 1.5,
                                      fontWeight: FontWeight.w700)),
                              const SizedBox(height: 16),
                              Text(addressController.text,
                                  style: const TextStyle(
                                      fontWeight: FontWeight.w700,
                                      fontSize: 16)),
                              const SizedBox(height: 12),
                              Text(
                                  '${fosseTypes.firstWhere((f) => f["value"] == selectedFosseType)["label"]} · ${selectedVolume.toInt()} m³',
                                  style: const TextStyle(
                                      color: AppColors.textMuted,
                                      fontSize: 13)),
                              const SizedBox(height: 8),
                              Text(
                                  'Priorité : ${urgencyLevels.firstWhere((u) => u["value"] == selectedUrgency)["label"]}',
                                  style: const TextStyle(
                                      color: AppColors.textMuted,
                                      fontSize: 13)),
                              const Divider(height: 32),
                              const Text('Montant estimé',
                                  style: TextStyle(
                                      color: AppColors.textMuted,
                                      fontSize: 12)),
                              const SizedBox(height: 8),
                              Text(_price,
                                  style: const TextStyle(
                                      fontSize: 28,
                                      fontWeight: FontWeight.w800,
                                      color: AppColors.primary)),
                              const SizedBox(height: 8),
                              const Text(
                                  'Le montant final dépend du volume réel.',
                                  style: TextStyle(
                                      fontSize: 11,
                                      color: AppColors.textMuted)),
                            ])),
                        const SizedBox(height: 24),
                        // Moyen de paiement
                        const Text(
                          "Moyen de paiement",
                          style: TextStyle(
                              fontSize: 16, fontWeight: FontWeight.w600),
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: paymentMethods.map((method) {
                            final value = method["value"] as String;
                            final label = method["label"] as String;
                            final icon = method["icon"] as IconData;
                            final isSelected = selectedPaymentMethod == value;

                            return Expanded(
                              child: Padding(
                                padding:
                                    const EdgeInsets.symmetric(horizontal: 4),
                                child: GestureDetector(
                                  onTap: () {
                                    setState(() {
                                      selectedPaymentMethod = value;
                                    });
                                  },
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(
                                        vertical: 16),
                                    decoration: BoxDecoration(
                                      color: isSelected
                                          ? AppColors.primary
                                          : Colors.white,
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(
                                        color: isSelected
                                            ? AppColors.primary
                                            : Colors.grey.shade300,
                                        width: isSelected ? 2 : 1,
                                      ),
                                    ),
                                    child: Column(
                                      children: [
                                        Icon(
                                          icon,
                                          size: 28,
                                          color: isSelected
                                              ? Colors.white
                                              : AppColors.textMuted,
                                        ),
                                        const SizedBox(height: 6),
                                        Text(
                                          label,
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.w600,
                                            color: isSelected
                                                ? Colors.white
                                                : AppColors.text,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            );
                          }).toList(),
                        ),

                        const SizedBox(height: 20),

                        // Choix du vidangeur (optionnel)
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              "Vidangeur (optionnel)",
                              style: TextStyle(
                                  fontSize: 16, fontWeight: FontWeight.w600),
                            ),
                            if (isLoadingDrivers)
                              const SizedBox(
                                width: 20,
                                height: 20,
                                child:
                                    CircularProgressIndicator(strokeWidth: 2),
                              )
                            else
                              IconButton(
                                icon: const Icon(Icons.refresh, size: 20),
                                onPressed: _loadDrivers,
                              ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          "Laissez vide pour attribution automatique",
                          style: TextStyle(
                              fontSize: 12, color: AppColors.textMuted),
                        ),
                        const SizedBox(height: 10),

                        if (drivers.isEmpty && !isLoadingDrivers)
                          _FormCard(
                            child: const Padding(
                              padding: EdgeInsets.all(16),
                              child: Text(
                                "Aucun vidangeur disponible",
                                textAlign: TextAlign.center,
                                style: TextStyle(color: AppColors.textMuted),
                              ),
                            ),
                          )
                        else
                          ...drivers.map((driver) => _DriverCard(
                                driver: driver,
                                isSelected: selectedDriver?.id == driver.id,
                                onTap: () {
                                  setState(() {
                                    if (selectedDriver?.id == driver.id) {
                                      selectedDriver = null;
                                    } else {
                                      selectedDriver = driver;
                                    }
                                  });
                                },
                              )),

                        const SizedBox(height: 20),
                      ],
                      if (errorMessage != null)
                        Padding(
                            padding: const EdgeInsets.only(top: 16),
                            child: Text(errorMessage!,
                                style: const TextStyle(
                                    color: AppColors.danger, fontSize: 12))),
                    ])),
              ])),
          bottomNavigationBar: SafeArea(
              key: ValueKey(step),
              top: false,
              child: Container(
                  padding: const EdgeInsets.fromLTRB(20, 12, 20, 16),
                  color: Colors.white,
                  child: PrimaryButton(
                    label: step == 2 ? 'Confirmer la demande' : 'Continuer',
                    isLoading: isLoading,
                    onPressed: isLoading || locating ? null : _next,
                  ))),
        ));
  }
}

class _FormCard extends StatelessWidget {
  final Widget child;

  const _FormCard({required this.child});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 12,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: child,
    );
  }
}

class _DriverCard extends StatelessWidget {
  final AvailableDriver driver;
  final bool isSelected;
  final VoidCallback onTap;

  const _DriverCard({
    required this.driver,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(
              color: isSelected ? AppColors.primary : Colors.transparent,
              width: isSelected ? 2 : 0,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.04),
                blurRadius: 12,
                offset: const Offset(0, 6),
              ),
            ],
          ),
          child: Row(
            children: [
              Container(
                width: 50,
                height: 50,
                decoration: BoxDecoration(
                  color: isSelected
                      ? AppColors.primary.withOpacity(0.1)
                      : AppColors.sand,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(
                  Icons.local_shipping,
                  color: isSelected ? AppColors.primary : AppColors.textMuted,
                  size: 28,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      driver.name,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      driver.vehicleType,
                      style: const TextStyle(
                        fontSize: 13,
                        color: AppColors.textMuted,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(Icons.location_on,
                            size: 14, color: AppColors.textMuted),
                        const SizedBox(width: 4),
                        Text(
                          driver.distanceFormatted,
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppColors.textMuted,
                          ),
                        ),
                        if (driver.tankCapacity != null) ...[
                          const SizedBox(width: 12),
                          const Icon(Icons.water_drop,
                              size: 14, color: AppColors.textMuted),
                          const SizedBox(width: 4),
                          Text(
                            driver.capacityLabel,
                            style: const TextStyle(
                              fontSize: 12,
                              color: AppColors.textMuted,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
              if (isSelected)
                const Icon(
                  Icons.check_circle,
                  color: AppColors.primary,
                  size: 24,
                ),
            ],
          ),
        ),
      ),
    );
  }
}
