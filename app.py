from flask import Flask, request, jsonify
from flask_cors import CORS
import pickle
import pandas as pd

app = Flask(__name__)
CORS(app)

# Load ML Models
try:
    participation_model = pickle.load(open("ml_service/model/participation_rf_model.pkl", "rb"))
    recommendation_model = pickle.load(open("ml_service/model/recommendation_rf_model.pkl", "rb"))
    print("✅ Models Loaded Successfully")
except Exception as e:
    print("❌ Model loading error:", str(e))

@app.route("/")
def home():
    return "Volunteer Management System ML Service is running!"

@app.route("/predict_participation", methods=["POST"])
def predict_participation():
    try:
        data = request.get_json()
        if not data:
            return jsonify({"error": "No input data provided"}), 400

        df = pd.DataFrame([data])
        prediction = participation_model.predict(df)[0]

        return jsonify({"prediction": int(prediction)})

    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route("/recommend_events", methods=["POST"])
def recommend_events():
    try:
        data = request.get_json()
        if not data:
            return jsonify({"error": "No input data provided"}), 400

        # Handle multiple events
        if 'events' in data and isinstance(data['events'], list):
            events_data = data['events']
            df = pd.DataFrame(events_data)
            predictions = recommendation_model.predict_proba(df)
            
            # Get probability of class 1 (will attend)
            if predictions.shape[1] > 1:
                scores = predictions[:, 1].tolist()
            else:
                scores = predictions[:, 0].tolist()
                
            return jsonify({"scores": scores})
        else:
            # Single event
            df = pd.DataFrame([data])
            predictions = recommendation_model.predict_proba(df)[0]
            score = float(predictions[1] if len(predictions) > 1 else predictions[0])
            return jsonify({"scores": [score]})

    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000, debug=True)