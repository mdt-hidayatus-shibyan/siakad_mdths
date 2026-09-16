import 'package:flutter/material.dart';
import '../../widgets/custom_app_bar.dart';
import '../ujian/input_nilai_tab_view.dart';

class PenilaianTab extends StatelessWidget {
  const PenilaianTab({super.key});

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      appBar: CustomAppBar(titleText: 'Input Nilai Ujian'),
      body: InputNilaiTabView(),
    );
  }
}
