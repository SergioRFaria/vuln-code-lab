import pickle


sample_obj = {
    "partner_name": "Acme Risk Analytics",
    "contact_email": "ops@acme-risk.example",
    "risk_tier": "medium",
    "enabled": True,
}


with open("sample_partner_record.pkl", "wb") as f:
    pickle.dump(sample_obj, f)

print("Wrote sample_partner_record.pkl")
