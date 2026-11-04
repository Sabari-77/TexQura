# 📖 TexQura End-User Operations Guide

Welcome to TexQura. This guide outlines the standard operational flow from receiving raw materials to issuing a final Quality Certificate.

## Phase 1: Master Data Entry (Admin/Manager)
Before inspecting fabrics, the core inventory data must exist in the system.
1. Log in as an **Administrator**.
2. Navigate to **Suppliers** and register your fabric vendors.
3. Navigate to **Fabrics** and define the types of materials you process (e.g., 100% Cotton, Polycotton).

## Phase 2: Logging Inventory (Manager)
When a delivery truck arrives at the factory, log the shipment.
1. Go to **Batches**. Click **New Batch**, select the Supplier and Fabric Type, and define the expected total length and roll count.
2. Go to **Fabric Rolls**. Break the batch down by registering the individual physical rolls (e.g., Roll 1: 500m, Roll 2: 450m) and assign them to the parent Batch.

## Phase 3: The Inspection Process (Inspector)
1. Log in as a **Quality Inspector**.
2. Go to **Fabric Rolls**. Find a roll marked *Awaiting Inspection* and click **Inspect**.
3. Measure the fabric on the machine. If a defect (Hole, Stain, Broken Yarn) is found, click **Add Defect**.
4. Enter the position of the defect on the roll and how many meters are affected. 
5. The system will live-calculate the defect percentage and preview the final Grade.
6. Click **Submit Inspection**. The roll's status changes to *Pending Approval*.

## Phase 4: Review & Certification (Manager)
1. Log in as a **Quality Manager**.
2. Navigate to the **Approvals** workspace. You will see all inspections awaiting your review.
3. Verify the defect breakdown.
4. If acceptable, click **Approve & Issue Certificate**. If the defect rate is too high, click **Reject Roll**.
5. Once approved, click **View Full Log** to print or download the digitally stamped **Certificate of Quality Assurance**.

## Phase 5: Handling Rejections (Admin/Manager)
1. If a roll was rejected, navigate to the **Complaints** module.
2. Select the rejected roll from the dropdown menu and document the severe issues.
3. The system will generate an official Complaint Ticket to send back to the Supplier for a refund or replacement.