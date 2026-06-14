from flask import Flask, request, render_template_string
from defusedxml import ElementTree as DefusedET

app = Flask(__name__)

PAGE = """
<!doctype html>
<html>
<head>
    <title>NorthBank Staff Import</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; color: #1f2937; }
        .header { background: #0f3d5e; color: white; padding: 20px 40px; font-size: 24px; font-weight: bold; }
        .container { max-width: 920px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 24px; }
        .summary-card { background: #eef6fb; border-radius: 10px; padding: 18px; }
        .label { color: #6b7280; font-size: 13px; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px; }
        .value { font-size: 24px; font-weight: bold; }
        .layout { display: grid; grid-template-columns: 1.05fr 0.95fr; gap: 24px; margin-top: 24px; }
        .panel { border: 1px solid #e5e7eb; border-radius: 10px; padding: 22px; }
        .subtle { color: #6b7280; }
        .notice { background: #ecfdf3; border: 1px solid #86efac; color: #166534; padding: 12px; border-radius: 6px; margin-top: 18px; }
        .error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 6px; margin-top: 18px; }
        form label { display: block; margin-top: 16px; font-weight: bold; }
        input[type=file] { width: 100%; padding: 10px; margin-top: 6px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        button { margin-top: 22px; background: #0f3d5e; color: white; padding: 12px 18px; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; }
        button:hover { background: #0b2f49; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border-bottom: 1px solid #e5e7eb; text-align: left; padding: 10px; }
        th { background: #eef6fb; width: 35%; }
        code { background: #f3f4f6; padding: 2px 5px; border-radius: 4px; }
        @media (max-width: 860px) { .summary, .layout { grid-template-columns: 1fr; } .container { margin: 20px; padding: 20px; } }
    </style>
</head>
<body>
    <div class="header">NorthBank Staff Import</div>
    <div class="container">
        <h1>Profile Provisioning Feed</h1>
        <p class="subtle">HR operations can import XML staff profiles before new joiners are provisioned across internal systems.</p>

        <div class="summary">
            <div class="summary-card"><div class="label">Pending Imports</div><div class="value">14</div></div>
            <div class="summary-card"><div class="label">Imported Today</div><div class="value">63</div></div>
            <div class="summary-card"><div class="label">Directory Sync SLA</div><div class="value">15m</div></div>
        </div>

        <div class="layout">
            <div class="panel">
                <h2>Upload Staff Profile</h2>
                <p class="subtle">Accepted format: XML staff profile export from the HR onboarding workflow.</p>

                <form method="POST" action="/upload" enctype="multipart/form-data">
                    <label for="xmlfile">Profile XML file</label>
                    <input id="xmlfile" type="file" name="xmlfile" accept=".xml,text/xml,application/xml">
                    <button type="submit">Import Profile</button>
                </form>

                {% if message %}
                    <div class="notice">{{ message }}</div>
                {% endif %}
                {% if error %}
                    <div class="error">{{ error }}</div>
                {% endif %}
            </div>

            <div class="panel">
                <h2>Last Parsed Record</h2>
                {% if profile %}
                    <table>
                        <tr><th>Employee Name</th><td>{{ profile.name }}</td></tr>
                        <tr><th>Email</th><td>{{ profile.email }}</td></tr>
                    </table>
                {% else %}
                    <p class="subtle">No profile has been imported in this session.</p>
                {% endif %}
                <p class="subtle" style="margin-top: 16px;">Sample fields expected by downstream provisioning: <code>name</code>, <code>email</code>.</p>
            </div>
        </div>
    </div>
</body>
</html>
"""


def render_page(profile=None, message=None, error=None):
    return render_template_string(PAGE, profile=profile, message=message, error=error)


@app.route('/')
def index():
    return render_page()

@app.route('/upload', methods=['POST'])
def upload():
    file = request.files.get('xmlfile')
    if not file:
        return render_page(error="No XML file was provided."), 400

    try:
        root = DefusedET.fromstring(file.read())

        name = root.findtext('name')
        email = root.findtext('email')

        profile = {"name": name, "email": email}
        return render_page(profile=profile, message="Profile imported successfully.")

    except Exception as e:
        return render_page(error=f"Import failed: {e}"), 400

if __name__ == '__main__':
    app.run(debug=True, port=8090)
