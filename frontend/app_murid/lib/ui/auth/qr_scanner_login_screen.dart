import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:provider/provider.dart';
import '../../core/theme/app_colors.dart';
import '../../core/utils/haptic_helper.dart';
import '../../providers/auth_provider.dart';
import '../../providers/dashboard_provider.dart';
import '../main/main_screen.dart';
import '../widgets/glass_card.dart';
import 'ganti_pin_awal_screen.dart';

class QrScannerLoginScreen extends StatefulWidget {
  const QrScannerLoginScreen({super.key});

  @override
  State<QrScannerLoginScreen> createState() => _QrScannerLoginScreenState();
}

class _QrScannerLoginScreenState extends State<QrScannerLoginScreen>
    with SingleTickerProviderStateMixin {
  late final MobileScannerController _scannerController;
  late final AnimationController _animController;
  late final Animation<double> _laserAnimation;

  bool _isProcessing = false;
  bool _isTorchOn = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _scannerController = MobileScannerController(
      detectionSpeed: DetectionSpeed.normal,
      facing: CameraFacing.back,
      torchEnabled: false,
    );

    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 2000),
    )..repeat(reverse: true);

    _laserAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animController, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _animController.dispose();
    _scannerController.dispose();
    super.dispose();
  }

  void _onDetect(BarcodeCapture capture) async {
    if (_isProcessing) return;

    final barcodes = capture.barcodes;
    if (barcodes.isEmpty) return;

    final rawValue = barcodes.first.rawValue;
    if (rawValue == null || rawValue.trim().isEmpty) return;

    setState(() {
      _isProcessing = true;
      _errorMessage = null;
    });

    HapticHelper.medium();

    final auth = context.read<AuthProvider>();
    final success = await auth.loginWithQr(rawValue.trim());

    if (!mounted) return;

    if (success) {
      HapticHelper.heavy();
      if (auth.currentWali?.isFirstLogin == true) {
        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(
            builder: (_) => const GantiPinAwalScreen(oldPin: '112233'),
          ),
          (route) => false,
        );
      } else {
        context.read<DashboardProvider>().fetchDashboard();
        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(builder: (_) => const MainScreen()),
          (route) => false,
        );
      }
    } else {
      HapticHelper.error();
      setState(() {
        _errorMessage =
            auth.errorMessage ??
            'QR Code tidak valid atau akun Wali Murid tidak ditemukan.';
      });
      _showErrorDialog(_errorMessage!);
    }
  }

  void _showErrorDialog(String message) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: const Row(
          children: [
            Icon(
              Icons.error_outline_rounded,
              color: AppColors.roseDanger,
              size: 26,
            ),
            SizedBox(width: 10),
            Text(
              'Gagal Memindai',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
            ),
          ],
        ),
        content: Text(
          message,
          style: const TextStyle(fontSize: 13, height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              Navigator.pop(context); // Kembali ke form login manual
            },
            child: const Text('Input Manual'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              setState(() {
                _isProcessing = false;
                _errorMessage = null;
              });
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primaryLight,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
            child: const Text('Pindai Ulang'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final scanBoxSize = MediaQuery.of(context).size.width * 0.72;
    final primaryColor = AppColors.primaryLight;

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // 1. Mobile Scanner Camera View
          MobileScanner(
            controller: _scannerController,
            onDetect: _onDetect,
            fit: BoxFit.cover,
          ),

          // 2. Custom Cutout Overlay
          CustomPaint(
            size: Size.infinite,
            painter: _ScannerOverlayPainter(
              boxSize: scanBoxSize,
              borderColor: primaryColor,
              borderWidth: 3.5,
              borderRadius: 20,
            ),
          ),

          // 3. Laser Animation inside Viewfinder Box
          Center(
            child: SizedBox(
              width: scanBoxSize,
              height: scanBoxSize,
              child: AnimatedBuilder(
                animation: _laserAnimation,
                builder: (context, child) {
                  return Stack(
                    children: [
                      Positioned(
                        top: _laserAnimation.value * (scanBoxSize - 20) + 10,
                        left: 12,
                        right: 12,
                        child: Container(
                          height: 3,
                          decoration: BoxDecoration(
                            gradient: LinearGradient(
                              colors: [
                                Colors.transparent,
                                primaryColor,
                                Colors.white,
                                primaryColor,
                                Colors.transparent,
                              ],
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: primaryColor.withValues(alpha: 0.8),
                                blurRadius: 10,
                                spreadRadius: 2,
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  );
                },
              ),
            ),
          ),

          // 4. Header Bar (Back, Flashlight, Switch Camera)
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _buildCircleIconButton(
                    icon: Icons.arrow_back_rounded,
                    onTap: () {
                      HapticHelper.selection();
                      Navigator.pop(context);
                    },
                  ),
                  Row(
                    children: [
                      _buildCircleIconButton(
                        icon: _isTorchOn
                            ? Icons.flash_on_rounded
                            : Icons.flash_off_rounded,
                        color: _isTorchOn ? Colors.amber : Colors.white,
                        onTap: () {
                          HapticHelper.light();
                          _scannerController.toggleTorch();
                          setState(() => _isTorchOn = !_isTorchOn);
                        },
                      ),
                      const SizedBox(width: 10),
                      _buildCircleIconButton(
                        icon: Icons.flip_camera_ios_rounded,
                        onTap: () {
                          HapticHelper.light();
                          _scannerController.switchCamera();
                        },
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          // 5. Instruction Bottom Glass Card
          Positioned(
            left: 24,
            right: 24,
            bottom: 40,
            child: GlassCard(
              padding: const EdgeInsets.all(20),
              borderRadius: 24,
              customBgColor: Colors.black.withValues(alpha: 0.65),
              customBorderColor: Colors.white24,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        width: 8,
                        height: 8,
                        decoration: BoxDecoration(
                          color: primaryColor,
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 8),
                      const Text(
                        'Pindai QR Code Kartu Murid',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w900,
                          color: Colors.white,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Posisikan QR Code yang tertera di belakang Kartu Murid / Akses Wali Murid di dalam kotak bidik.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 11,
                      color: Colors.white70,
                      height: 1.4,
                    ),
                  ),
                ],
              ),
            ),
          ),

          // 6. Processing / Loading Overlay
          if (_isProcessing && _errorMessage == null)
            Container(
              color: Colors.black.withValues(alpha: 0.75),
              child: Center(
                child: GlassCard(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 28,
                    vertical: 24,
                  ),
                  borderRadius: 24,
                  customBgColor: Colors.black.withValues(alpha: 0.8),
                  customBorderColor: primaryColor.withValues(alpha: 0.5),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      CircularProgressIndicator(
                        color: primaryColor,
                        strokeWidth: 3,
                      ),
                      const SizedBox(height: 18),
                      const Text(
                        'Mengautentikasi Akun...',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 14,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'Memverifikasi QR Code Kartu Murid',
                        style: TextStyle(color: Colors.white60, fontSize: 11),
                      ),
                    ],
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildCircleIconButton({
    required IconData icon,
    required VoidCallback onTap,
    Color color = Colors.white,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 44,
        height: 44,
        decoration: BoxDecoration(
          color: Colors.black.withValues(alpha: 0.5),
          shape: BoxShape.circle,
          border: Border.all(color: Colors.white24, width: 1),
        ),
        child: Center(child: Icon(icon, color: color, size: 20)),
      ),
    );
  }
}

/// Custom Painter untuk membuat cutout mask hitam dengan sudut viewfinder neon
class _ScannerOverlayPainter extends CustomPainter {
  final double boxSize;
  final Color borderColor;
  final double borderWidth;
  final double borderRadius;

  _ScannerOverlayPainter({
    required this.boxSize,
    required this.borderColor,
    required this.borderWidth,
    required this.borderRadius,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final backgroundPaint = Paint()
      ..color = Colors.black.withValues(alpha: 0.6)
      ..style = PaintingStyle.fill;

    final center = Offset(size.width / 2, size.height / 2);
    final rect = Rect.fromCenter(
      center: center,
      width: boxSize,
      height: boxSize,
    );
    final rrect = RRect.fromRectAndRadius(rect, Radius.circular(borderRadius));

    // Draw background mask with cutout hole
    final path = Path()
      ..addRect(Rect.fromLTWH(0, 0, size.width, size.height))
      ..addRRect(rrect)
      ..fillType = PathFillType.evenOdd;

    canvas.drawPath(path, backgroundPaint);

    // Draw corner markers
    final borderPaint = Paint()
      ..color = borderColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = borderWidth
      ..strokeCap = StrokeCap.round;

    final cornerLength = boxSize * 0.12;

    // Top-Left Corner
    canvas.drawLine(
      Offset(rect.left, rect.top + cornerLength),
      Offset(rect.left, rect.top + borderRadius),
      borderPaint,
    );
    canvas.drawArc(
      Rect.fromLTWH(rect.left, rect.top, borderRadius * 2, borderRadius * 2),
      3.14159,
      1.57079,
      false,
      borderPaint,
    );
    canvas.drawLine(
      Offset(rect.left + borderRadius, rect.top),
      Offset(rect.left + cornerLength, rect.top),
      borderPaint,
    );

    // Top-Right Corner
    canvas.drawLine(
      Offset(rect.right - cornerLength, rect.top),
      Offset(rect.right - borderRadius, rect.top),
      borderPaint,
    );
    canvas.drawArc(
      Rect.fromLTWH(
        rect.right - borderRadius * 2,
        rect.top,
        borderRadius * 2,
        borderRadius * 2,
      ),
      -1.57079,
      1.57079,
      false,
      borderPaint,
    );
    canvas.drawLine(
      Offset(rect.right, rect.top + borderRadius),
      Offset(rect.right, rect.top + cornerLength),
      borderPaint,
    );

    // Bottom-Left Corner
    canvas.drawLine(
      Offset(rect.left, rect.bottom - cornerLength),
      Offset(rect.left, rect.bottom - borderRadius),
      borderPaint,
    );
    canvas.drawArc(
      Rect.fromLTWH(
        rect.left,
        rect.bottom - borderRadius * 2,
        borderRadius * 2,
        borderRadius * 2,
      ),
      1.57079,
      1.57079,
      false,
      borderPaint,
    );
    canvas.drawLine(
      Offset(rect.left + borderRadius, rect.bottom),
      Offset(rect.left + cornerLength, rect.bottom),
      borderPaint,
    );

    // Bottom-Right Corner
    canvas.drawLine(
      Offset(rect.right - cornerLength, rect.bottom),
      Offset(rect.right - borderRadius, rect.bottom),
      borderPaint,
    );
    canvas.drawArc(
      Rect.fromLTWH(
        rect.right - borderRadius * 2,
        rect.bottom - borderRadius * 2,
        borderRadius * 2,
        borderRadius * 2,
      ),
      0,
      1.57079,
      false,
      borderPaint,
    );
    canvas.drawLine(
      Offset(rect.right, rect.bottom - borderRadius),
      Offset(rect.right, rect.bottom - cornerLength),
      borderPaint,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
