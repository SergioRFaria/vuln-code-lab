package main

import (
	"fmt"
	"io/ioutil"
	"log"
	"net/http"
	"path/filepath"
	"strings"
)

const baseDir = "./files"

func indexHandler(w http.ResponseWriter, r *http.Request) {
	html := `<!doctype html>
<html>
<head>
    <title>NorthBank Statement Viewer</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; color: #1f2937; }
        .header { background: #0f3d5e; color: white; padding: 20px 40px; font-size: 24px; font-weight: bold; }
        .container { max-width: 960px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 24px; }
        .summary-card { background: #eef6fb; border-radius: 10px; padding: 18px; }
        .label { color: #6b7280; font-size: 13px; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px; }
        .value { font-size: 24px; font-weight: bold; }
        .layout { display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 24px; margin-top: 24px; }
        .panel { border: 1px solid #e5e7eb; border-radius: 10px; padding: 22px; }
        .subtle { color: #6b7280; }
        form label { display: block; margin-top: 16px; font-weight: bold; }
        input { width: 100%; padding: 10px; margin-top: 6px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 15px; }
        button { margin-top: 22px; background: #0f3d5e; color: white; padding: 12px 18px; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; }
        button:hover { background: #0b2f49; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom: 1px solid #e5e7eb; text-align: left; padding: 10px; }
        th { background: #eef6fb; }
        code { background: #f3f4f6; padding: 2px 5px; border-radius: 4px; }
        @media (max-width: 860px) { .summary, .layout { grid-template-columns: 1fr; } .container { margin: 20px; padding: 20px; } }
    </style>
</head>
<body>
    <div class="header">NorthBank Statement Viewer</div>
    <div class="container">
        <h1>Archived Statement Access</h1>
        <p class="subtle">Support agents can retrieve stored statement previews by internal file identifier during customer call handling.</p>

        <div class="summary">
            <div class="summary-card"><div class="label">Archived Statements</div><div class="value">148</div></div>
            <div class="summary-card"><div class="label">Views Today</div><div class="value">89</div></div>
            <div class="summary-card"><div class="label">Retention Tier</div><div class="value">Internal</div></div>
        </div>

        <div class="layout">
            <div class="panel">
                <h2>Open Statement File</h2>
                <p class="subtle">Enter the stored file name from the archive index to load a plain-text preview.</p>
                <form method="GET" action="/view">
                    <label for="file">Stored file name</label>
                    <input id="file" name="file" value="example.txt" spellcheck="false">
                    <button type="submit">Open Statement</button>
                </form>
            </div>

            <div class="panel">
                <h2>Recent Archive Entries</h2>
                <table>
                    <tr><th>File</th><th>Type</th></tr>
                    <tr><td>example.txt</td><td>Preview snapshot</td></tr>
                    <tr><td>monthly-summary.txt</td><td>Statement digest</td></tr>
                </table>
                <p class="subtle" style="margin-top: 16px;">Viewer endpoint: <code>/view?file=...</code></p>
            </div>
        </div>
    </div>
</body>
</html>`

	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	w.Write([]byte(html))
}

func viewHandler(w http.ResponseWriter, r *http.Request) {
	filename := r.URL.Query().Get("file")

	if filename == "" {
		http.Error(w, "Missing file parameter", http.StatusBadRequest)
		return
	}

	cleanPath := filepath.Clean(filepath.Join(baseDir, filename))
	if !strings.HasPrefix(cleanPath, filepath.Clean(baseDir)) {
		http.Error(w, "Access denied", http.StatusForbidden)
		return
	}

	data, err := ioutil.ReadFile(cleanPath)
	if err != nil {
		http.Error(w, fmt.Sprintf("Error reading file: %v", err), http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "text/plain")
	w.Write(data)
}

func main() {
	http.HandleFunc("/", indexHandler)
	http.HandleFunc("/view", viewHandler)

	fmt.Println("Server running at http://localhost:8090/")
	log.Fatal(http.ListenAndServe(":8090", nil))
}
