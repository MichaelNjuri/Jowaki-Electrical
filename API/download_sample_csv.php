<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sample_stock_items.csv"');

// Sample CSV data based on the JOWAKI STOCK ITEMS format
$sampleData = [
    ['ITEM NAME', 'CATEGORY'],
    ['KR101 Small', 'ACCESS'],
    ['Bolt electric lock GAL-607A', 'ACCESS'],
    ['Digital access keypad BLACK', 'ACCESS'],
    ['Maglock 180kgs', 'ACCESS'],
    ['Adaptor 24v', 'ADAPTER'],
    ['Adaptors 12v 1A', 'ADAPTER'],
    ['Battery 12V 2.3ah YUASA', 'BATTERY'],
    ['Battery 12V 7Ah CSB', 'BATTERY'],
    ['Break Glass BLUE', 'BREAKGLASS'],
    ['Break Glass GREEN', 'BREAKGLASS'],
    ['Exit button plastic slim', 'BUTTON'],
    ['Alarm cable 4-core', 'CABLE'],
    ['Coaxial cable RG59 100m drum', 'CABLE'],
    ['2MP IP camera bullet', 'CAMERA'],
    ['DVR 16 CHN', 'CAMERA'],
    ['Hard disk 1TB', 'CAMERA'],
    ['MIFARE CARD', 'CARD'],
    ['RFID card', 'CARD'],
    ['PIR comet', 'DETECTOR'],
    ['Beam sensor small', 'DETECTOR'],
    ['DRUID 15', 'ENERGIZER'],
    ['Corner post Galvanised', 'FENCE'],
    ['Energizer DRUID13 LCD', 'FENCE'],
    ['HT wire 1.6mm 25kgs', 'FENCE'],
    ['Alarm Bell 12v- 6" DC', 'FIRE'],
    ['Fire cable 1.5mm 100m CCA', 'FIRE'],
    ['Fire panel 12 zones NO batt', 'FIRE'],
    ['Garret handheld metal detector', 'GARRET'],
    ['Centurion sliding Gate D20 SMART', 'GATE'],
    ['Gate lock', 'GATE'],
    ['BAOFENG BF-888S Radio call', 'GENERALS'],
    ['CABLE TIES', 'GENERALS'],
    ['Guard tour GAP-6200D', 'GUARD'],
    ['DS-2CD1021G0-1 2MP IP Bullet Normal IR', 'HIK'],
    ['DS-7104HGHI DVR', 'HIK'],
    ['Keyguard switch', 'KEYSWITCH'],
    ['DOUBLE TELESCOPIC aluminium ladder (1.9+1.9)', 'LADDER'],
    ['Magnet Angle metallic', 'MAGNET'],
    ['CAT 6 Cable', 'NETWORK'],
    ['Patch panel 24 port', 'NETWORK'],
    ['Accenta alarm panel', 'PANEL'],
    ['Premier 816', 'PANEL'],
    ['Video door phone T-908C + 01C', 'PHONE'],
    ['PSU 1.5amps psu', 'PSU'],
    ['PSU Access 3amps', 'PSU'],
    ['Reader F21', 'Readers'],
    ['Reader MB 360', 'Readers'],
    ['Energizer Repair', 'Repairs'],
    ['Analyser Aritech', 'SENSOR'],
    ['Postage', 'SERVICES'],
    ['Receiver-MB4000', 'SHERLOTRONICS'],
    ['Siren 15watts', 'SIREN'],
    ['Strobe light Big ES80', 'STROBE'],
    ['Door exit switch metallic big NO/NC', 'SWITCHES'],
    ['2Mp IP Bullet AK series', 'TIANDY'],
    ['TV 32"', 'TV'],
    ['TV 55"', 'TV']
];

// Output CSV
$output = fopen('php://output', 'w');
foreach ($sampleData as $row) {
    fputcsv($output, $row);
}
fclose($output);
?> 