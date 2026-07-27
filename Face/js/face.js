import { FaceRecognizer } from "../modules/face.js";

const recognizer = new FaceRecognizer("video", "overlay", "result");
document.getElementById("startRecognition").addEventListener("click", () => {
  recognizer.startRecognition();
});
