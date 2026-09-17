<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Services\Verification\VerificationWorkflowService;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\Company\VerificationController;
use App\Http\Requests\Api\V1\Company\SelectEntityTypeRequest;
use App\Http\Requests\Api\V1\Company\UpdateVerificationDetailsRequest;
use App\Http\Requests\Api\V1\Company\SubmitVerificationApplicationRequest;

echo "=== TESTING MOBILE VERIFICATION API ENDPOINTS ===\n";

$company = Company::firstOrCreate(['slug' => 'api-test-corp'], ['name' => 'API Test Corp', 'country' => 'IN', 'primary_email' => 'apitest@corp.com']);
$user = User::firstOrCreate(['email' => 'apitest@corp.com'], ['name' => 'API Tester', 'password' => bcrypt('secret'), 'company_id' => $company->id]);

auth()->login($user);
$service = app(VerificationWorkflowService::class);
$controller = new VerificationController($service);

// 1. GET /api/v1/company/verification
$req = Request::create('/api/v1/company/verification', 'GET');
$req->setUserResolver(fn () => $user);
$res1 = $controller->index($req);
echo "[1] GET index response status: " . $res1->getStatusCode() . "\n";

// 2. POST /api/v1/company/verification/entity-type
$req2 = SelectEntityTypeRequest::create('/api/v1/company/verification/entity-type', 'POST', ['business_type' => 'Private / public company']);
$req2->setUserResolver(fn () => $user);
$res2 = $controller->selectEntityType($req2);
echo "[2] POST entity-type status: " . $res2->getStatusCode() . "\n";

// 3. POST /api/v1/company/verification/details
$req3 = UpdateVerificationDetailsRequest::create('/api/v1/company/verification/details', 'POST', [
    'legal_name' => 'API Test Corp Private Limited',
    'display_name' => 'API Test WA',
    'category' => 'Technology',
    'website' => 'https://apitestcorp.com',
    'address' => '456 Innovation Way, Tech Park',
    'signatory_name' => 'API Tester',
    'signatory_designation' => 'Managing Director',
    'business_email' => 'contact@apitestcorp.com',
    'business_phone' => '+2348000001111',
    'wa_phone' => '+2348000002222',
]);
$req3->setUserResolver(fn () => $user);
$res3 = $controller->updateDetails($req3);
echo "[3] POST details status: " . $res3->getStatusCode() . "\n";

// 4. POST /api/v1/company/verification/submit
$req4 = SubmitVerificationApplicationRequest::create('/api/v1/company/verification/submit', 'POST', ['confirm_declaration' => true]);
$req4->setUserResolver(fn () => $user);
$res4 = $controller->submit($req4);
echo "[4] POST submit status: " . $res4->getStatusCode() . "\n";

$data = json_decode($res4->getContent(), true);
echo "[SUCCESS] Submitted Application Status: " . ($data['data']['status'] ?? 'N/A') . "\n";
echo "=== MOBILE API VERIFICATION SUCCESSFUL ===\n";
