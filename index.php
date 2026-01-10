<?php
// Deploy Test - CyberPanel
echo "<!DOCTYPE html>";
echo "<html lang='tr'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Deploy Test</title>";
echo "    <style>";
echo "        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }";
echo "        .container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); text-align: center; }";
echo "        h1 { color: #667eea; margin: 0 0 20px 0; }";
echo "        .success { color: #10b981; font-size: 60px; margin-bottom: 20px; }";
echo "        .info { background: #f3f4f6; padding: 15px; border-radius: 5px; margin-top: 20px; }";
echo "        .info p { margin: 5px 0; color: #374151; }";
echo "    </style>";
echo "</head>";
echo "<body>";
echo "    <div class='container'>";
echo "        <div class='success'>✅</div>";
echo "        <h1>Deploy Başarılı!</h1>";
echo "        <p>CyberPanel PHP sistemi düzgün çalışıyor.</p>";
echo "        <div class='info'>";
echo "            <p><strong>PHP Versiyonu:</strong> " . phpversion() . "</p>";
echo "            <p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "            <p><strong>Tarih:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "            <p><strong>Repo:</strong> alimustpdr/riba-survey-system</p>";
echo "        </div>";
echo "    </div>";
echo "</body>";
echo "</html>";
?>