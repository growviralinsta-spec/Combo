<?php
session_start();
const DATA_FILE = __DIR__ . '/data.json';

function loadData() {
    if (!file_exists(DATA_FILE)) {
        $data = [
            'settings' => [
                'api_url' => 'https://mrsmm.org/api/v2',
                'api_key' => 'PUT_YOUR_NEW_MRSMM_API_KEY_HERE',
                'admin_password' => 'CHANGE_THIS_PASSWORD'
            ],
            'services' => []
        ];
        file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
    }
    return json_decode(file_get_contents(DATA_FILE), true) ?: ['settings'=>[],'services'=>[]];
}
function saveData($data) {
    file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}
$data = loadData();

if (isset($_POST['login'])) {
    if (hash_equals((string)$data['settings']['admin_password'], (string)($_POST['password'] ?? ''))) {
        $_SESSION['admin_ok'] = true;
        header('Location: admin.php'); exit;
    }
    $error = 'Wrong password.';
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }

if (!empty($_SESSION['admin_ok'])) {
    if (isset($_POST['save_settings'])) {
        $data['settings']['api_url'] = trim($_POST['api_url'] ?? '');
        if (trim($_POST['api_key'] ?? '') !== '') $data['settings']['api_key'] = trim($_POST['api_key']);
        if (trim($_POST['admin_password'] ?? '') !== '') $data['settings']['admin_password'] = trim($_POST['admin_password']);
        saveData($data); $saved = 'Settings saved.';
    }

    if (isset($_POST['save_service'])) {
        $id = trim($_POST['service_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $details = trim($_POST['details'] ?? '');
        $active = isset($_POST['active']);

        if ($id !== '' && $name !== '') {
            $found = false;
            foreach ($data['services'] as &$s) {
                if ((string)$s['id'] === $id) {
                    $s = ['id'=>$id,'name'=>$name,'price'=>$price,'description'=>$desc,'details'=>$details,'active'=>$active];
                    $found = true; break;
                }
            }
            unset($s);
            if (!$found) $data['services'][] = ['id'=>$id,'name'=>$name,'price'=>$price,'description'=>$desc,'details'=>$details,'active'=>$active];
            saveData($data); $saved = $found ? 'Service updated.' : 'Service added.';
        } else $error = 'Service ID and name are required.';
    }

    if (isset($_GET['delete'])) {
        $del = (string)$_GET['delete'];
        $data['services'] = array_values(array_filter($data['services'], fn($s)=>(string)$s['id'] !== $del));
        saveData($data); header('Location: admin.php'); exit;
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>SMM Admin</title>
<style>
body{margin:0;background:#080808;color:#fff;font-family:Arial,sans-serif}.wrap{max-width:1000px;margin:auto;padding:24px}
.card{background:#111;border:1px solid #282828;border-radius:18px;padding:22px;margin:16px 0}h1,h2{margin-top:0}.muted{color:#999;font-size:13px}
input,textarea{width:100%;box-sizing:border-box;background:#0a0a0a;color:#fff;border:1px solid #333;border-radius:10px;padding:12px;margin:7px 0 14px}
button,.btn{background:linear-gradient(135deg,#7c3aed,#db2777);color:#fff;border:0;border-radius:10px;padding:12px 16px;font-weight:800;cursor:pointer}.danger{background:#421616}.row{display:grid;grid-template-columns:1fr 1fr;gap:14px}.service{border:1px solid #292929;border-radius:14px;padding:15px;margin:10px 0;display:flex;justify-content:space-between;gap:12px;align-items:center}.tag{font-size:11px;padding:5px 8px;border-radius:99px;background:#153b22;color:#6ee7a0}.off{background:#333;color:#aaa}
@media(max-width:650px){.row{grid-template-columns:1fr}.service{display:block}.service .actions{margin-top:10px}}
</style></head><body><div class="wrap">
<?php if(empty($_SESSION['admin_ok'])): ?>
<div class="card" style="max-width:400px;margin:80px auto"><h1>SMM Admin</h1><p class="muted">Login to manage services & API settings.</p>
<?php if(!empty($error)) echo '<p style="color:#ff7777">'.$error.'</p>'; ?>
<form method="post"><input type="password" name="password" placeholder="Admin password" required><button name="login">Login</button></form></div>
<?php else: ?>
<div style="display:flex;justify-content:space-between;align-items:center"><div><h1>SMM Admin Panel</h1><div class="muted">Manage your combos and provider settings.</div></div><a class="btn" href="?logout=1">Logout</a></div>
<?php if(!empty($saved)) echo '<div class="card" style="border-color:#244b31;color:#8df0ae">'.$saved.'</div>'; ?>
<?php if(!empty($error)) echo '<div class="card" style="border-color:#542222;color:#ff9999">'.$error.'</div>'; ?>

<div class="card"><h2>API Settings</h2><p class="muted">API key is stored server-side in data.json. Never put it in smm.html.</p>
<form method="post"><label>API Endpoint</label><input name="api_url" value="<?=htmlspecialchars($data['settings']['api_url'])?>">
<label>New API Key <span class="muted">(leave blank to keep current)</span></label><input type="password" name="api_key" placeholder="Paste new key">
<label>New Admin Password <span class="muted">(leave blank to keep current)</span></label><input type="password" name="admin_password" placeholder="Change admin password">
<button name="save_settings">Save Settings</button></form></div>

<div class="card"><h2>Add / Edit Service</h2><p class="muted">Use your provider's actual service ID. The ID is also used by the order endpoint.</p>
<form method="post"><div class="row"><div><label>MrSMM Service ID</label><input name="service_id" placeholder="e.g. 1234" required></div><div><label>Display Name</label><input name="name" placeholder="Instagram Growth Combo" required></div></div>
<div class="row"><div><label>Price</label><input name="price" placeholder="₹99"></div><div><label>Short Description</label><input name="description" placeholder="Fast growth package"></div></div>
<label>Package Details (one item per line)</label><textarea name="details" rows="4" placeholder="10,000 Views&#10;500 Likes&#10;1,000 Shares"></textarea>
<label><input type="checkbox" name="active" checked style="width:auto"> Active on website</label><br><br><button name="save_service">Save Service</button></form></div>

<div class="card"><h2>Services</h2>
<?php if(empty($data['services'])): ?><p class="muted">No services added yet.</p><?php endif; ?>
<?php foreach($data['services'] as $s): ?>
<div class="service"><div><b><?=htmlspecialchars($s['name'])?></b><div class="muted">Provider ID: <?=htmlspecialchars($s['id'])?> · <?=htmlspecialchars($s['price'])?></div><div class="muted"><?=nl2br(htmlspecialchars($s['details']))?></div></div>
<div class="actions"><span class="tag <?=empty($s['active'])?'off':''?>"><?=empty($s['active'])?'INACTIVE':'ACTIVE'?></span> <a class="btn danger" href="?delete=<?=urlencode($s['id'])?>" onclick="return confirm('Delete this service?')">Delete</a></div></div>
<?php endforeach; ?>
</div>
<div class="card"><p class="muted">Security: protect this folder and keep <b>data.json</b> inaccessible from the public web if your hosting allows it.</p></div>
<?php endif; ?></div></body></html>