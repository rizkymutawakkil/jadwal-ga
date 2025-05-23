<?php
// Definisikan jam untuk pola 222-23
function getJamArray() {
    return [
        ['07:30', '09:10', 'J001'],
        ['09:10', '10:50', 'J002'],
        ['10:50', '12:00', 'J003'],
        ['12:40', '14:20', 'J004'],
        ['14:20', '16:00', 'J005']
    ];
}

// Fungsi untuk memilih jam berdasarkan SKS untuk pola 222-23
function selectTimeSlot($sks, $jam_array) {
    if ($sks == 3) {
        // Untuk mata kuliah 3 SKS, gunakan slot terakhir (slot 5)
        if (isset($jam_array[4])) {
            return $jam_array[4];
        }
        return null;
    } else if ($sks == 2) {
        // Untuk mata kuliah 2 SKS, gunakan slot 1-4
        $available_slots = array_slice($jam_array, 0, 4);
        if (empty($available_slots)) {
            return null;
        }
        // Pilih slot secara berurutan, bukan acak
        static $current_slot = 0;
        $slot = $available_slots[$current_slot];
        $current_slot = ($current_slot + 1) % 4;
        return $slot;
    } else if ($sks == 4) {
        // Untuk mata kuliah 4 SKS, gunakan slot yang tersedia
        if (empty($jam_array)) {
            return null;
        }
        // Pilih slot secara acak dari semua slot yang tersedia
        return $jam_array[array_rand($jam_array)];
    }
    return null;
}

// Fungsi untuk membuat kromosom khusus pola 222-23
function createChromosome22223($mata_kuliah, $ruangan) {
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