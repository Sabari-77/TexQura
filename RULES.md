# ⚖️ System Rules, Regulations & Constraints

TexQura operates under strict enterprise business logic to ensure data integrity and prevent fraudulent quality assurance reporting. All users must adhere to the following system constraints.

## 1. The Maker-Checker Security Protocol
To ensure unbiased quality control, TexQura enforces a strict separation of duties:
* **The Maker (Inspector):** Only users with the Quality Inspector role can examine physical fabric rolls, log defect measurements, and submit the roll for review.
* **The Checker (Manager):** Only users with the Quality Manager role can review, approve, or reject an inspection.
* **Hard Constraint:** The system explicitly blocks the account that created an inspection from approving that same inspection. Fraudulent self-approval is impossible.

## 2. Automated Grading Thresholds
Fabric grades are determined mathematically by the system, eliminating human bias.
* **Grade A (Premium):** Total defect length is ≤ 1.00% of the total roll length.
* **Grade B (Standard):** Total defect length is > 1.00% but ≤ 3.00% of the total roll length.
* **Grade C (Rejected):** Total defect length exceeds 3.00%. These rolls cannot be approved and are flagged for Supplier Complaints.
*(Note: Administrators can adjust these percentage thresholds dynamically in the Admin panel).*

## 3. Data Integrity & Validation Rules
* **Defect Boundaries:** A defect's `position` + `affected length` mathematically cannot exceed the total registered length of the fabric roll. The frontend and backend will block any anomalies.
* **Unique Identifiers:** Supplier Codes, Fabric Codes, Batch Numbers, and Roll Numbers must be 100% unique. Duplicates will trigger a PDO constraint violation and be rejected by the database.
* **Irreversible Approvals:** Once a Manager approves a roll and generates a Certificate of Quality Assurance, the inspection record is permanently locked to preserve the audit trail.