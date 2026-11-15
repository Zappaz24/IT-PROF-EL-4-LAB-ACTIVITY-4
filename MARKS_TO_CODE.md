# From marks to code

**App:** CampusPrint Hub — the printing service of a university campus.
**Concept marked:** `PrintClient` (the PIM, unchanged, is `app/PIM/printclient.json`).
**Generator:** `app/Commands/ModelGenerate.php` (Activity 4 version, which reads a marking file).

## 1. Which file is the marking model, and why are the marks not written inside the model file?

The marking model is **`app/PIM/printclient.marks.json`**, sitting next to the model file
`app/PIM/printclient.json`. A second one, `app/PIM/printclient.marksB.json`, marks the same model for a
different situation.

The marks are not inside the model because the model file is the PIM, and the PIM only says what
CampusPrint Hub knows: a print client has a `clientName`, a `studentNumber`, a `contactEmail`, a
`courseCode`, a `yearLevel`, and is or is not enrolled. All of that is true on any platform. The table
name, which column must never repeat, and how long a column is are decisions of the platform — here PHP
on CodeIgniter 4 with SQLite — not facts about the business. If those decisions were written in the
model, the same design could not be used for another platform, the two copies would drift apart every
time the design changed, and the generator could no longer reject a mark that does not match the model.
Keeping the marks in a separate list is what lets the model file stay byte-for-byte the same while
`printclient.marksB.json` produces different code.

## 2. Which mark selected a rule, and which marks provided a value?

| Mark | Where it goes | Kind | What it decides |
|------|---------------|------|-----------------|
| `unique` | on an attribute, e.g. `studentNumber` | **Rule selection** | `true` makes the generator apply the unique-key rule to that column (an `addUniqueKey` line in the migration, `UNIQUE` in the SQL). `false`, or no mark, leaves the rule off. |
| `tableName` | on the entity, `PrintClient` | **Value provider** | Fills the table name. Without it the generator invents `print_clients`. |
| `size` | on a text attribute, e.g. `clientName` | **Value provider** | Fills the length of the text column. Without it the length is the default 255. |

`unique` is the only mark that changes *which rule* the generator uses. `tableName` and `size` only fill
in a blank that the rule already has.

## 3. Trace table — every mark in the first marking file

| Mark | Target | Value | Where it shows in the generated code |
|------|--------|-------|--------------------------------------|
| `tableName` | `PrintClient` | `campusprint_clients` | Migration `app/Database/Migrations/<timestamp>_CreatePrintClientTable.php`, line 22: `$this->forge->createTable('campusprint_clients');` (and line 27 in `down()`). Model class `app/Models/PrintClientModel.php`, line 9: `protected $table = 'campusprint_clients';`. SQL script `writable/generated/campusprint_clients.sql`, line 1: `CREATE TABLE campusprint_clients (`. |
| `unique` | `studentNumber` | `true` | Migration, line 21: `$this->forge->addUniqueKey('student_number');`. SQL script, line 4: `student_number VARCHAR(15) NOT NULL UNIQUE`. |
| `size` | `clientName` | `80` | Migration, line 13: `'client_name' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false]`. SQL script, line 3: `client_name VARCHAR(80) NOT NULL`. |
| `size` | `studentNumber` | `15` | Migration, line 14: `'student_number' => ['type' => 'VARCHAR', 'constraint' => 15, ...]`. SQL script, line 4: `VARCHAR(15)`. |
| `size` | `contactEmail` | `120` | Migration, line 15: `'contact_email' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true]`. SQL script, line 5: `contact_email VARCHAR(120)`. |

Unmarked attributes keep the default rule: `courseCode` gets `VARCHAR 255` (migration line 16, SQL line 6),
`yearLevel` gets `INT 11` (line 17), `isEnrolled` gets `TINYINT 1` (line 18). The generated
`PrintClientModel.php` lists the same six columns in `$allowedFields` (line 11).

## 4. The model file did not change between the first and second marking files. What changed in the output?

`app/PIM/printclient.json` is untouched — same entity, same six fields, same types, same `required`
flags. What changed in the output of `php spark model:generate app/PIM/printclient.json sql app/PIM/printclient.marksB.json`:

| | First file | Second file |
|---|---|---|
| Table name | `campusprint_clients` | `counter_clients` |
| Unique key | on `student_number` | on `contact_email` |
| `client_name` length | `VARCHAR(80)` | `VARCHAR(40)` |
| `student_number` length | `VARCHAR(15)` | `VARCHAR(10)` |
| `contact_email` length | `VARCHAR(120)` | `VARCHAR(255)` (the `size` mark was removed, so the default came back) |

Everything else is identical because it comes from the model: the same seven columns in the same order,
the same `NOT NULL` markers, the same `id` primary key, and the same `$allowedFields` list. Only the
table name, one `UNIQUE`, and three lengths moved — exactly the marks.

## 5. Which marks in Part 3 of the Marking Models Documentation match the marks in the marking file?

All five match, name for name and value for value: `tableName = campusprint_clients` on `PrintClient`,
`unique = true` on `studentNumber`, and `size` 80 / 15 / 120 on `clientName`, `studentNumber`,
`contactEmail`.

Nothing is different. The only addition is in the **second** file, `printclient.marksB.json`, which puts
`unique` on `contactEmail` instead of `studentNumber` because the lab (Part 5.2) asks for the unique mark
on a different field. That is the same situation written in Part 4 of the documentation form — the
single-window counter — so the document and the code still agree.

---

## Proof the command was run on a real CodeIgniter 4 install

Run on PHP 8.4.27 with **CodeIgniter 4.7.6** and SQLite.

```
$ php spark model:generate app/PIM/printclient.json sql
Marking file: app/PIM/printclient.marks.json
Mark applied: tableName = "campusprint_clients" on PrintClient
Mark applied: unique = true on studentNumber
Mark applied: size = 80 on clientName
Mark applied: size = 15 on studentNumber
Mark applied: size = 120 on contactEmail
Wrote writable/generated/campusprint_clients.sql

$ php spark migrate
Running all new migrations...
        Running: (App) ..._CreatePrintClientTable
Migrations complete.

$ php spark db:table campusprint_clients --metadata
| client_name    | VARCHAR | | No  | | No |
| student_number | VARCHAR | | No  | | No |
| contact_email  | VARCHAR | | Yes | | No |
| course_code    | VARCHAR | | No  | | No |
```

The unique mark is not only written, it is enforced. Asking SQLite directly:

```
index: campusprint_clients_student_number  (unique = 1)
second INSERT with the same student number:
REFUSED: UNIQUE constraint failed: campusprint_clients.student_number
```

Both refusals fired as well, and neither one wrote a file:

```
The mark unique is on "contactEmial", which is not a field in the model.
size can only be used on a text field, and yearLevel is wholenumber.
```

> Line numbers above refer to the generated files in `expected-output/`. The timestamp in the migration
> file name is different on every run.
