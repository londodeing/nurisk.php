import re

with open('mobile/app/lib/features/operasi/assessment/presentation/screens/assessment_wizard_screen.dart', 'r') as f:
    content = f.read()

start_marker = "    if (currentStep == 0) {"
end_marker = "        valid = true;\n      }\n    } else if (currentStep == 6) {"

new_block = """    if (currentStep == 0) {
      if (_formKey1.currentState!.validate()) {
        notifier.updateFormData({
          'jenis_laporan': _jenisLaporan,
          'id_kecamatan': _idKec,
          'id_desa': _idDesa,
          'alamat_spesifik': _alamatController.text,
          'latitude': _latitudeController.text,
          'longitude': _longitudeController.text,
          'event_date': _eventDateController.text,
          'event_time': _eventTimeController.text,
        });
        valid = true;
      }
    } else if (currentStep == 1) {
      if (_formKey2.currentState!.validate()) {
        notifier.updateFormData({
          'dampak_manusia': {
            'meninggal': int.tryParse(_meninggalController.text) ?? 0,
            'hilang': int.tryParse(_hilangController.text) ?? 0,
            'luka_berat': int.tryParse(_lukaBeratController.text) ?? 0,
            'luka_ringan': int.tryParse(_lukaRinganController.text) ?? 0,
            'dampak_manusia': int.tryParse(_menderitaMengungsiController.text) ?? 0,
            'pengungsi_jiwa': int.tryParse(_pengungsiJiwaController.text) ?? 0,
            'pengungsi_kk': int.tryParse(_pengungsiKkController.text) ?? 0,
          }
        });
        valid = true;
      }
    } else if (currentStep == 2) {
      if (_formKey3.currentState!.validate()) {
        notifier.updateFormData({
          'dampak_rumah': {
            'berat': int.tryParse(_rumahBeratController.text) ?? 0,
            'sedang': int.tryParse(_rumahSedangController.text) ?? 0,
            'ringan': int.tryParse(_rumahRinganController.text) ?? 0,
          },
          'dampak_fasum': {
            'sanitas': int.tryParse(_fasumSanitasiController.text) ?? 0,
            'pendidikan': int.tryParse(_fasumPendidikanController.text) ?? 0,
            'kesehatan': int.tryParse(_fasumKesehatanController.text) ?? 0,
            'ibadah': int.tryParse(_fasumIbadahController.text) ?? 0,
            'komunikasi': _komunikasiPutus ? 1 : 0,
            'listrik': int.tryParse(_listrikPadamController.text) ?? 0,
            'kantor': int.tryParse(_fasumPerkantoranController.text) ?? 0,
            'jembatan': (int.tryParse(_fasumJembatanPutusController.text) ?? 0) + (int.tryParse(_fasumJembatanRusakController.text) ?? 0),
            'pasar': int.tryParse(_fasumPasarController.text) ?? 0,
            'spbu': int.tryParse(_fasumSpbuController.text) ?? 0,
          },
          'dampak_vital': {
            'air': _airBersihRusak ? 1 : 0,
            'listrik': int.tryParse(_listrikPadamController.text) ?? 0,
            'telkom': _komunikasiPutus ? 1 : 0,
            'irigasi': double.tryParse(_irigasiRusakController.text) ?? 0.0,
            'jalan': double.tryParse(_jalanRusakController.text) ?? 0.0,
            'spbu': int.tryParse(_fasumSpbuController.text) ?? 0,
          }
        });
        valid = true;
      }
    } else if (currentStep == 3) {
      if (_formKey4.currentState!.validate()) {
        notifier.updateFormData({
          'dampak_lingkungan': {
            'sawah': double.tryParse(_sawahRusakController.text) ?? 0.0,
            'hutan': double.tryParse(_hutanTerdampakController.text) ?? 0.0,
            'unggas': int.tryParse(_ternakUnggasController.text) ?? 0,
            'kaki_empat': int.tryParse(_ternakKakiEmpatController.text) ?? 0,
            'perikanan_kolam': double.tryParse(_perikananKolamController.text) ?? 0.0,
            'perikanan_nelayan': int.tryParse(_perikananNelayanController.text) ?? 0,
          }
        });
        valid = true;
      }
    } else if (currentStep == 4) {
      if (_formKey5.currentState!.validate()) {
        notifier.updateFormData({
          'dampak_ekonomi': {
            'persentase': _persentaseEkonomi,
            'sektor_1': _sektorPencaharian1Controller.text,
            'kontribusi_1': double.tryParse(_kontribusi1Controller.text) ?? 0.0,
            'status_1': _statusTerdampak1,
            'sektor_2': _sektorPencaharian2Controller.text,
            'kontribusi_2': double.tryParse(_kontribusi2Controller.text) ?? 0.0,
            'status_2': _statusTerdampak2,
            'sektor_3': _sektorPencaharian3Controller.text,
            'kontribusi_3': double.tryParse(_kontribusi3Controller.text) ?? 0.0,
            'status_3': _statusTerdampak3,
            'distribusi': _distribusiPanen,
            'fasilitas': _fasilitasPengolahan,
          }
        });
        valid = true;
      }
    } else if (currentStep == 5) {
      if (_formKey6.currentState!.validate()) {
        Map<String, String> needsNumeric = {};
        _kebutuhanControllers.forEach((id, ctrl) {
          final val = double.tryParse(ctrl.text);
          if (val != null && val > 0) {
            final m = ref.read(assessmentProvider).numerikMaster.firstWhere((x) => x.idItem == id);
            needsNumeric[m.kodeItem] = val.toString();
          }
        });
        
        notifier.updateFormData({
          'kondisi_mutakhir': _kondisiUmumController.text,
          'upaya_penanganan': _upayaPenangananController.text,
          'sebaran_dampak': _sebaranDampakController.text,
          'kendala_lapangan': _kendalaLapanganController.text,
          'kendala_tambahan': _kendalaTambahanController.text,
          'rekomendasi_aksi': _rekomendasiAksiController.text,
          'kebutuhan': {
            'relawan': _kebRelawanController.text,
            'logistik': _kebLogistikController.text,
            'peralatan': _kebPeralatanController.text,
            'medis': _kebMedisController.text,
            'pangan': _kebPanganController.text,
            'lainnya': _kebLainnyaController.text,
          },
          'needs_numeric': needsNumeric,
        });
        valid = true;
      }
    }"""

idx_start = content.find(start_marker)
idx_end = content.find(end_marker)

if idx_start != -1 and idx_end != -1:
    new_content = content[:idx_start] + new_block + content[idx_end:]
    with open('mobile/app/lib/features/operasi/assessment/presentation/screens/assessment_wizard_screen.dart', 'w') as f:
        f.write(new_content)
    print("Replaced submit logic successfully")
else:
    print("Failed to find markers")
