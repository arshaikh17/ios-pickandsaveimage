//
//  ViewController.swift
//  PickAndSaveImage
//
//  Created by Mostafizur Rahman on 5/18/18.
//  Copyright © 2018 Mostafizur Rahman. All rights reserved.
//

import UIKit
import Photos
import CoreData

class ViewController: UIViewController {
    
    var fileName: String = ""
    var manageObjectContext: NSManagedObjectContext!
    
    override func viewDidLoad() {
        super.viewDidLoad()
        manageObjectContext = (UIApplication.shared.delegate as! AppDelegate).persistentContainer.viewContext
        
        readImageInfo()
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
    }
    
    @IBAction func loadImageButtonTapped(sender: UIButton) {
        let imagePicker = UIImagePickerController()
        imagePicker.delegate = self
        
        imagePicker.allowsEditing = false
        imagePicker.sourceType = .photoLibrary
        
        present(imagePicker, animated: true, completion: nil)
    }
}

extension ViewController : UIImagePickerControllerDelegate, UINavigationControllerDelegate {
    
    func imagePickerController(_ picker: UIImagePickerController, didFinishPickingMediaWithInfo info: [String : Any]) {
        
        var assetURL: String = "", fileName: String = "", creationTime: String = "", modificationTime: String = ""
        
        if let url = info[UIImagePickerControllerReferenceURL] as? URL {
            assetURL = url.absoluteString
            let assets = PHAsset.fetchAssets(withALAssetURLs: [url], options: nil)
            if let firstAsset = assets.firstObject,
                let firstResource = PHAssetResource.assetResources(for: firstAsset).first {
                fileName = firstResource.originalFilename
                if let date = firstAsset.creationDate{
                    creationTime = date.toString(dateFormat: "dd-MM-YYYY")
                }
                if let date = firstAsset.modificationDate{
                    modificationTime = date.toString(dateFormat: "dd-MM-YYYY")
                }
            } else {
                fileName = generateNameForImage()
            }
        } else {
            fileName = generateNameForImage()
        }
        
        print("File path = \(assetURL)")
        print("File name = \(fileName)")
        print("Creation time = \(creationTime)")
        print("Modification time = \(modificationTime)")
        self.saveImageInfo(assetURL, fileName: fileName, creationTime: creationTime, modificationTime: modificationTime)
        dismiss(animated: true)
    }
    
    func imagePickerControllerDidCancel(_ picker: UIImagePickerController) {
        dismiss(animated: true)
    }
    
    func generateNameForImage()->String {
        return "IMG_random_string"
    }
    
    func saveImageInfo(_ assetURL:String, fileName:String, creationTime:String, modificationTime:String){
        let entity = NSEntityDescription.entity(forEntityName: "ImageInfo",
                                                in: manageObjectContext)
        let options = NSManagedObject(entity: entity!,
                                      insertInto:manageObjectContext)
        
        options.setValue(assetURL, forKey: "filePath")
        options.setValue(fileName, forKey: "fileName")
        options.setValue(creationTime, forKey: "createdAt")
        options.setValue(modificationTime, forKey: "updatedAt")
        do {
            try manageObjectContext.save()
        } catch {
            print("Failed saving")
        }
    }
    
    func readImageInfo(){
        
        let request = NSFetchRequest<NSFetchRequestResult>(entityName: "ImageInfo")
        request.returnsObjectsAsFaults = false
        do {
            let result = try manageObjectContext.fetch(request)
            for data in result as! [NSManagedObject] {
                print(data.value(forKey: "filePath") as! String)
                print(data.value(forKey: "fileName") as! String)
                print(data.value(forKey: "createdAt") as! String)
                print(data.value(forKey: "updatedAt") as! String)
            }
            
        } catch {
            
            print("Failed")
        }
    }
}

extension Date
{
    func toString( dateFormat format  : String ) -> String{
        let dateFormatter = DateFormatter()
        dateFormatter.dateFormat = format
        return dateFormatter.string(from: self)
    }
    
}

