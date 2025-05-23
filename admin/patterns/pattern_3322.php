<?php
// Definisikan jam untuk pola 33-22
function getJamArray() {
    return [
        ['07:30', '10:00', 'J001'],
        ['10:00', '12:00', 'J002'],
        ['12:40', '14:20', 'J003'],
        ['14:20', '16:00', 'J004']
    ];
}

// Fungsi untuk memilih jam berdasarkan SKS untuk pola 33-22
function selectTimeSlot($sks, $jam_array) {
    if ($sks == 3) {
        // Pilih dari slot 3 jam (J001 atau J002)
        $three_hour_slots = array_slice($jam_array, 0, 2);
        return $three_hour_slots[array_rand($three_hour_slots)];
    } else {
        // Pilih dari slot 2 jam (J003 atau J004)
        $two_hour_slots = array_slice($jam_array, 2, 2);
        return $two_hour_slots[array_rand($two_hour_slots)];
    }
}

// Fungsi untuk membuat kromosom khusus pola 33-22
function createChromosome3322($mata_kuliah, $ruangan) {
    $chromosome = [];
    $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    $jam_array = getJamArray();
    
    foreach ($mata_kuliah as $mk) {
        // Validasi data mata kuliah
        if (empty($mk['kode_mk']) || empty($mk['kode_jurusan']) || empty($mk['kelas'])) {
            continue;
        }
        
        // Split kelas into array
        $kelas_list = explode(',', $mk['kelas']);
        
        foreach ($kelas_list as $kelas) {
            $kelas = trim($kelas);
            if (empty($kelas)) continue;
            
            // Get available rooms for this department
            $available_rooms = array_filter($ruangan, function($r) use ($mk) {
                return (isset($r['kode_jurusan']) && $r['kode_jurusan'] === $mk['kode_jurusan']) || 
                       (isset($r['kode_jurusan']) && $r['kode_jurusan'] === null);
            });
            
            if (empty($available_rooms)) {
                continue;
            }
            
            // Randomly select a room from available rooms
            $selected_room = $available_rooms[array_rand($available_rooms)];
            
            // Randomly select a day
            $selected_day = $hari[array_rand($hari)];
            
            // Select time slot based on SKS
            $time_slot = selectTimeSlot($mk['sks'], $jam_array);
            if (!$time_slot) {
                continue;
            }
            
            // Create schedule entry
            $schedule = [
                'kode_mk' => $mk['kode_mk'],
                'kode_jurusan' => $mk['kode_jurusan'],
                'kode_ruangan' => $selected_room['kode_ruangan'],
                'hari' => $selected_day,
                'jam_mulai' => $time_slot[0],
                'jam_selesai' => $time_slot[1],
                'kode_jam' => $time_slot[2],
                'kelas' => $kelas
            ];
            
            $chromosome[] = $schedule;
        }
    }
    
    return $chromosome;
}
?> 