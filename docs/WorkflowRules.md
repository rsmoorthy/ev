# Workflow Rules

## Case study from PRS

* Get Configuration Data  (Method)
* Massage Data
  - Name, Firstname and Lastname  (Condition Check and Action)
  - Remove invalid participant data
  - Group and obtain Participants (Method to group RHS)
* Validations:
  - Program Open (LHS vs RHS)
  - Isha Programs Check (LHS vs RHS)
* Referral Code:
  - Match Referral Code or Get New Referral Code (Method)
  - Based on Referral Code, Check Availability  (Condition Check)
  - Apply Discount (Action)
* Program Cost
  - Compute Program Cost (Method)

* Blacklist Check
  - Refer Service and Check Match (Condition Check)
  - Block if so (Action)

* Check Quota
  - Refer Service and Check Match (Condition Check)
  - Reject if full (Action)
  - Allocate quota if available (Action)

* Medical Ailment Check
  - Check (Condition Check)
  - Hold if yes (Action)

* Send Online Payment Link
  - Check various conditions (Check)
  - Send if yes (Action)

* Overseas
  - If configured (Check)
  - Push to OCO (Action)

* Confirmation
  - Check various conditions (Check)
  - If yes, Set "Confirmed" (Action)
  - If yes, push to CICO (Action)

* Confirmation Email / SMS
  - Check and Send  (Check / Action)

* Payments handling
  - TODO -- need to check how to classify
