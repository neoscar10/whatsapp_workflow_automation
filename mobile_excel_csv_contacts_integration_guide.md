# Mobile Integration Guide: Excel & CSV Contacts Export, Template Download, and Import

## 1. Executive Overview

This guide details the mobile API implementation for:
1. **Latest Contact Ordering**: `GET /api/v1/contacts` returns contacts ordered by `created_at DESC` so the last added contact appears at index 0.
2. **Dual-Format Export (Excel `.xlsx` & CSV `.csv`)**: Eliminates phone number formatting bugs (e.g. scientific notation `1.23E+10` or stripped `+` prefixes) when opening spreadsheets.
3. **Dual-Format Sample Template Download**: Allows mobile users to download pre-formatted `.xlsx` or `.csv` sample files.
4. **Excel & CSV File Import**: Supports uploading `.xlsx`, `.xls`, `.csv`, and `.txt` files up to **10MB** via `POST /api/v1/contacts/import`.

---

## 2. API Endpoints Specification

### 2.1 List Contacts (Latest Added First)
Returns all contacts for the authenticated user's company, ordered by `created_at DESC, id DESC`.

* **HTTP Method**: `GET`
* **Endpoint**: `/api/v1/contacts`
* **Headers**:
  * `Authorization`: `Bearer <SANCTUM_TOKEN>`
  * `Accept`: `application/json`
* **Query Parameters** (Optional):
  * `page` (integer): Page number (default: `1`)
  * `per_page` (integer): Items per page (default: `15`)
  * `search` (string): Search string (name or phone)
* **Response `HTTP 200` Example**:
```json
{
  "success": true,
  "message": "Contacts retrieved successfully.",
  "data": {
    "data": [
      {
        "id": 104,
        "name": "Jane Doe (Last Added)",
        "phone": "19876543210",
        "normalized_phone": "19876543210",
        "status": "active",
        "source": "manual",
        "has_opted_in": true,
        "do_not_message": false,
        "created_at": "2026-09-18T18:00:00Z"
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 5,
      "total": 68
    }
  }
}
```

---

### 2.2 Download Contacts Export File (`.xlsx` or `.csv`)
Downloads the exported contacts file in Excel (`.xlsx`) or CSV (`.csv`) format.

* **HTTP Method**: `GET`
* **Endpoint**: `/api/v1/contacts/export`
* **Headers**:
  * `Authorization`: `Bearer <SANCTUM_TOKEN>`
* **Query Parameters**:
  * `format` (string, optional): `xlsx` (default) or `csv`
* **Content Types Returned**:
  * For `format=xlsx`: `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
  * For `format=csv`: `text/csv; charset=UTF-8`

---

### 2.3 Download Sample Import Template (`.xlsx` or `.csv`)
Downloads a sample template containing pre-formatted headers and example rows.

* **HTTP Method**: `GET`
* **Endpoint**: `/api/v1/contacts/import/template`
* **Headers**:
  * `Authorization`: `Bearer <SANCTUM_TOKEN>`
* **Query Parameters**:
  * `format` (string, optional): `xlsx` (default) or `csv`
* **Content Types Returned**:
  * For `format=xlsx`: `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
  * For `format=csv`: `text/csv; charset=UTF-8`

---

### 2.4 Import Contacts File (Excel `.xlsx` / `.xls` or CSV `.csv` / `.txt`)
Uploads a spreadsheet or CSV file to bulk create or update contacts.

* **HTTP Method**: `POST`
* **Endpoint**: `/api/v1/contacts/import`
* **Headers**:
  * `Authorization`: `Bearer <SANCTUM_TOKEN>`
  * `Accept`: `application/json`
  * `Content-Type`: `multipart/form-data`
* **Body Form Data**:
  * `file`: The selected binary file (`.xlsx`, `.xls`, `.csv`, `.txt`, max 10MB)
* **Response `HTTP 200` Example**:
```json
{
  "success": true,
  "message": "Contacts imported successfully.",
  "data": {
    "total_rows": 25,
    "created": 20,
    "updated": 5,
    "skipped": 0,
    "failed": 0,
    "errors": []
  }
}
```

---

## 3. Template Column Requirements

Whether uploading `.xlsx` or `.csv`, the file can include the following columns:

| Column Name | Required? | Description | Example Values |
| :--- | :---: | :--- | :--- |
| `phone` | **Yes** | Phone number in ITU-T E.164 or clean format | `+1234567890`, `1234567890` |
| `name` | No | Full contact display name | `John Doe` |
| `tags` | No | Comma-separated list of tag names | `Customer,VIP,Lead` |
| `groups` | No | Comma-separated list of group names | `Newsletter,Promotions` |
| `notes` | No | Additional notes/remarks | `Requested product demo` |
| `has_opted_in` | No | Opt-in consent boolean | `true`, `false`, `1`, `0` |

---

## 4. Flutter (Dart) Mobile Code Implementation

Below are complete Dart methods for Flutter mobile app integration using the `dio` package and `path_provider`.

### 4.1 Required Dependencies (`pubspec.yaml`)
```yaml
dependencies:
  dio: ^5.4.0
  file_picker: ^8.0.0
  path_provider: ^2.1.2
```

### 4.2 Contact API Service Class (`contact_api_service.dart`)
```dart
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:path_provider/path_provider.dart';

class ContactApiService {
  final Dio _dio;
  final String baseUrl;
  String? _authToken;

  ContactApiService({required this.baseUrl, String? authToken})
      : _authToken = authToken,
        _dio = Dio(BaseOptions(
          baseUrl: baseUrl,
          headers: {
            'Accept': 'application/json',
            if (authToken != null) 'Authorization': 'Bearer $authToken',
          },
        ));

  /// 1. Fetch contacts list (Ordered by latest added first)
  Future<Map<String, dynamic>> fetchContacts({int page = 1, String? search}) async {
    final response = await _dio.get('/api/v1/contacts', queryParameters: {
      'page': page,
      if (search != null && search.isNotEmpty) 'search': search,
    });
    return response.data;
  }

  /// 2. Download Export File (.xlsx or .csv)
  Future<File> downloadExport({required String format}) async {
    final cleanFormat = format.toLowerCase() == 'csv' ? 'csv' : 'xlsx';
    final tempDir = await getTemporaryDirectory();
    final savePath = '${tempDir.path}/contacts_export_${DateTime.now().millisecondsSinceEpoch}.$cleanFormat';

    final response = await _dio.get(
      '/api/v1/contacts/export',
      queryParameters: {'format': cleanFormat},
      options: Options(
        responseType: ResponseType.bytes,
        headers: {'Authorization': 'Bearer $_authToken'},
      ),
    );

    final file = File(savePath);
    await file.writeAsBytes(response.data as List<int>);
    return file;
  }

  /// 3. Download Sample Template (.xlsx or .csv)
  Future<File> downloadSampleTemplate({required String format}) async {
    final cleanFormat = format.toLowerCase() == 'csv' ? 'csv' : 'xlsx';
    final tempDir = await getTemporaryDirectory();
    final savePath = '${tempDir.path}/contacts_template.$cleanFormat';

    final response = await _dio.get(
      '/api/v1/contacts/import/template',
      queryParameters: {'format': cleanFormat},
      options: Options(
        responseType: ResponseType.bytes,
        headers: {'Authorization': 'Bearer $_authToken'},
      ),
    );

    final file = File(savePath);
    await file.writeAsBytes(response.data as List<int>);
    return file;
  }

  /// 4. Upload & Import File (.xlsx, .xls, .csv, .txt)
  Future<Map<String, dynamic>> importContactsFile(File file) async {
    final fileName = file.path.split('/').last;
    final formData = FormData.fromMap({
      'file': await MultipartFile.fromFile(
        file.path,
        filename: fileName,
      ),
    });

    final response = await _dio.post(
      '/api/v1/contacts/import',
      data: formData,
      options: Options(
        headers: {
          'Authorization': 'Bearer $_authToken',
          'Accept': 'application/json',
        },
      ),
    );

    return response.data;
  }
}
```

---

## 5. Mobile UI Workflow Recommendations

1. **Format Picker Bottom Sheet / Dialog**:
   - Present the mobile user with a format selection dialog when tapping **Export** or **Download Template**:
     - `Excel (.xlsx)` (Recommended - pre-styled, avoids scientific notation)
     - `CSV (.csv)` (Universal text file)
2. **File Import Picker**:
   - Use `FilePicker.platform.pickFiles(allowedExtensions: ['xlsx', 'xls', 'csv', 'txt'], type: FileType.custom)` to let users select files from device storage or cloud drives.
3. **Import Feedback Screen**:
   - Display summary stats: `Created: X`, `Updated: Y`, `Skipped: Z`, `Failed: F`.
   - If `errors` array is not empty, display error messages per row so the user can fix formatting on device.
