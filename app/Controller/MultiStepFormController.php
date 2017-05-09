<?php
class MultiStepFormController extends AppController {
	var $components = array('Wizard.Wizard');
        public $uses = array('Registereduser', 'Post' , 'MultiStepForm','Applicant', 'Applicantext', 'Education','Experience',
                            'Academic_dist','Image','Document', 'Researchpaper', 'Researcharticle','Researchproject', 
                            'Experiencephd', 'ApiScore', 'NewAPIScore');
        
        function beforeFilter() {
            if(!$this->Session->check('registration_id')) {
                $this->redirect(array('controller' => 'users', 'action' => 'dashboard'));
            }
            $current_datetime = new DateTime();
            $current_datetime->setTimezone(new DateTimeZone('Asia/Calcutta'));
            $close_datetime = new DateTime("2017-05-09 17:00:00", new DateTimeZone('Asia/Calcutta'));
            //print_r($current_datetime->format('Y-m-d-H-i-s'));
            //print_r($close_datetime->format('Y-m-d-H-i-s'));
            $applicant = $this->Applicant->find('all', array(
                        'conditions' => array('Applicant.registration_id' => $this->Session->read('registration_id'))));
            if ($current_datetime > $close_datetime) {
                $this->Session->setFlash('Application Form is closed.');
                
                $this->redirect(array('controller' => 'form', 'action' => 'generalinformation'));
            }
            $this->Wizard->steps = array('first','second','third','fourth','fifth','sixth', 'seventh', 'eighth', 'ninth');
        }
        
        function wizard($step = null) {
            if($this->Session->check('registration_id')) {
                //debug($this->alreadyAppliedCheck());
                //debug($this->isApplicantIdValid()); return false;
                $this->alreadyAppliedCheck();
                $this->isApplicantIdValid();
                $this->Wizard->process($step);
            }
            else {
                $this->redirect(array('controller' => 'users', 'action' => 'dashboard'));
            }
	}
        
        function alreadyAppliedCheck() {
            $posts_applied = $this->Applicant->find('all', array(
                        'conditions' => array('Applicant.registration_id' => $this->Session->read('registration_id'))));
            $final_submit_posts = array();
            $mismatch = false;
            foreach ($posts_applied as $key => $value) {
                if ($posts_applied[$key]['Applicant']['post_applied_for'] == $this->getPostAppliedFor()
                        //&& $posts_applied[$key]['Applicant']['area'] == $this->getAreaAppliedFor()
                        && $posts_applied[$key]['Applicant']['centre'] == $this->getCentreAppliedFor()) {
                    if ($posts_applied[$key]['Applicant']['final_submit'] == "1") {
                        // redirect to general page & disable the post
                        $mismatch = true;
                    }
                }
            }
            if ($mismatch == true) {
                $this->Session->setFlash('You have already applied for and final submitted the selected Post/Centre.');
                $this->redirect(array('controller' => 'form', 'action' => 'generalinformation'));
            }
        }
        
        private function getApplicantIdAsPerPostAreaCentre() {
            $reg_id = $this->Session->read('registration_id');
            if(!empty($reg_id)) {
                $applicants = $this->Applicant->find('all', array(
                            'conditions' => array('Applicant.registration_id' => $reg_id,
                                                  'Applicant.post_applied_for' => $this->getPostAppliedFor(),
                                                  'Applicant.centre' => $this->getCentreAppliedFor()
                                                  )));

                //print_r($applicants);
                if(count($applicants) == 0) {
                    $this->Applicant->create();
                    $this->Applicant->set(['registration_id' => $reg_id,
                                           'post_applied_for' => $this->getPostAppliedFor(),
                                           'centre' => $this->getCentreAppliedFor()]);
                    $this->Applicant->save();
                    return $this->Applicant->getLastInsertID();
                }
                else if(count($applicants) == 1)
                    return $applicants['0']['Applicant']['id'];
                else {
                    //this condition should not arise. delete all the above records
                    $deleted = $this->Applicant->deleteAll( array('Applicant.registration_id' => $reg_id,
                                                                        'Applicant.post_applied_for' => $this->getPostAppliedFor(),
                                                                        'Applicant.centre' => $this->getCentreAppliedFor()
                                                                        ));
                    $this->Session->setFlash('Multiple entries found for applied posts. Please contact support.');
                    $this->redirect(array('controller' => 'users', 'action' => 'logout'));
                }
            }
        }
        
        // This function should be called after alreadyappliedcheck
        private function isApplicantIdValid() {
            if(!empty($this->Session->read('applicant_id'))) {
                $applicantId = $this->getApplicantIdAsPerPostAreaCentre();
                if($applicantId == $this->Session->read('applicant_id')) {
                    return true;
                }
                else {
                    $this->Session->write('applicant_id', $applicantId);
                }
            }
            else {
                $applicantId = $this->getApplicantIdAsPerPostAreaCentre();
                $this->Session->write('applicant_id', $applicantId);
                return true;
            }
        }
        
        function _prepareFirst() {
        if (!empty($this->Session->read('registration_id')) && !empty($this->Session->read('applicant_id'))) {
            $registration_data = $this->Registereduser->find('all', array(
                'conditions' => array('Registereduser.id' => $this->Session->read('registration_id'))));
            $applicants = $this->Applicant->find('all', array(
                'conditions' => array('Applicant.id' => $this->Session->read('applicant_id'))));
            if (count($applicants) == 1) {
                $applicants['0']['Applicant']['post_applied_for'] = $this->getPostAppliedFor();
                $applicants['0']['Applicant']['centre'] = $this->getCentreAppliedFor();
                //$applicants['0']['Applicant']['area'] = $this->getAreaAppliedFor();
                $this->request->data = $applicants['0'];
                $this->Session->write('MultiStepForm.applicantId', $applicants['0']['Applicant']['id']);
                $maritalStatusSelected = $applicants['0']['Applicant']['marital_status'];
                $postAppliedFor = $applicants['0']['Applicant']['post_applied_for'];
                //$category = $applicants['0']['Applicant']['category'];
                $gender = $applicants['0']['Applicant']['gender'];
                $physically_disabled = $applicants['0']['Applicant']['physically_disabled'];
                $this->set('maritalStatusSelected', $maritalStatusSelected);
                //$this->set('category', $category);
                $this->set('gender', $gender);
                $this->set('physically_disabled', $physically_disabled);
                $this->set('postAppliedFor', $postAppliedFor);
            } else if ($continue == false) {
                $this->Session->setFlash('The form has been submitted and cannot be modified.');
                $this->redirect(array('controller' => 'form', 'action' => 'generalinformation'));
            }
        } else {
            $this->redirect(array('controller' => 'users', 'action' => 'logout'));
        }
    }

    function _processFirst() {
            $this->Applicant->create();    
            $this->Applicant->set($this->data);
            if($this->Applicant->validates()) { //&& $this->User->validates()) {
                $this->Applicant->save();
                return true;
            }
            return false;
	}
        
        function _prepareSecond() {
            if (!empty($this->Session->read('applicant_id'))) {
                $temp = $this->Session->read('applicant_id');
                $education_arr = $this->Education->find('all', array(
                        'conditions' => array('Education.applicant_id' => $this->Session->read('applicant_id'))));
                $misc = $this->Applicant->find('all', array(
                        'conditions' => array('Applicant.id' => $this->Session->read('applicant_id'))));
                //print_r($this->Session->read('applicant_id'));
                //if(count($education_arr) == 7 || count($education_arr) == 12) {
                    //$this->request->data = $education_arr;
                    $educationId_arr = array();
                    $education_data = array();
                    foreach($education_arr as $key => $value){
                        $educationId_arr[$key] = $value['Education']['id'];
                        $education_data[$key] = $education_arr[$key]['Education'];
                    }
                    $this->request->data = array('Education' => $education_data,
                                                  'Applicant' => !empty($misc) ?  $misc['0']['Applicant'] : array());
                    //$this->Session->write('MultiStepForm.educationId_arr', $educationId_arr);
                //}
                //else if(count($education_arr) > 7) {
                //    $this->Session->setFlash('An error has occured. Please contact Support.');
                //}
            }
	    else {
		$this->redirect(array('controller' => 'users', 'action' => 'logout'));
	    }
            
        }
        
        function _processSecond($count = 1) {
            $rows = $this->Education->find('all', array('conditions' => array('Education.applicant_id' => $this->Session->read('applicant_id'))));
            //debug($this->Session->read('applicant_id')); debug($rows);
            if(count($rows) == 12 && empty($this->data['Education'][0]['id'])) {
                $this->Session->setFlash('An error has occured. Please logout and login again.');
                //$this->redirect(array('controller' => 'users', 'action' => 'logout'));
                return false;
            }
            if($this->Education->saveMany($this->data['Education']) && $this->Applicant->save($this->data['Applicant'])) { 
                return true;
            }
            return false;
	}
        
        function _prepareThird() {
            //if ($this->Auth->loggedIn()) {
                $data = array();
                $exp_arr = $this->Experience->find('all', array(
                        'conditions' => array('Experience.applicant_id' => $this->Session->read('applicant_id'))));
                $expphd_arr = $this->Experiencephd->find('all', array(
                        'conditions' => array('Experiencephd.applicant_id' => $this->Session->read('applicant_id'))));
                $misc = $this->Applicant->find('all', array(
                        'conditions' => array('Applicant.id' => $this->Session->read('applicant_id'))));
                $expId_arr = array();
                $exp_data = array();
                foreach($exp_arr as $key => $value){
                    $expId_arr[$key] = $value['Experience']['id'];
                    $exp_data[$key] = $exp_arr[$key]['Experience'];
                }
                $data['Experience'] = $exp_data;
                
                $expphdId_arr = array();
                $expphd_data = array();
                foreach($expphd_arr as $key => $value){
                    $expphdId_arr[$key] = $value['Experiencephd']['id'];
                    $expphd_data[$key] = $expphd_arr[$key]['Experiencephd'];
                }
                $data['Experiencephd'] = $expphd_data;
                
                if(count($misc) == 1) {
                    $data['Applicant'] = $misc['0']['Applicant'];
                    //$this->Session->write('MultiStepForm.miscexpId', $misc['0']['Misc']['id']);
                }
                else if(count($misc) > 1) {
                    $this->Session->setFlash('An error has occured. Please contact Support.');
                }
                
                $this->request->data = $data;
            //}
            
        }
        
        function _processThird($count = 1) {
            $rows = $this->Experiencephd->find('all', array('conditions' => array('Experiencephd.applicant_id' => $this->Session->read('applicant_id'))));
            if(count($rows) == 7 && empty($this->data['Experiencephd'][0]['id'])) {
                $this->Session->setFlash('An error has occured. Please logout and login again.');
                return false;
            }
            if($this->data['modified'] == 'true') {
                $this->Experience->deleteAll( array('Experience.applicant_id' => $this->Session->read('applicant_id')));
            }
            
            if($this->Experience->saveMany($this->data['Experience']) && $this->Applicant->save($this->data['Applicant'])
                    && $this->Experiencephd->saveMany($this->data['Experiencephd'])) {
                return true;
            }
            return false;
	}
        
        function _prepareFourth() {
            $temp = $this->Session->read('applicant_id');
            $researchpaper_arr = $this->Researchpaper->find('all', array(
                    'conditions' => array('Researchpaper.applicant_id' => $this->Session->read('applicant_id'))));
            $researchpaperId_arr = array();
            $researchpaper_data = array();
            foreach($researchpaper_arr as $key => $value){
                $researchpaperId_arr[$key] = $value['Researchpaper']['id'];
                $researchpaper_data[$key] = $researchpaper_arr[$key]['Researchpaper'];
            }
            $researcharticle_arr = $this->Researcharticle->find('all', array(
                    'conditions' => array('Researcharticle.applicant_id' => $this->Session->read('applicant_id'))));
            $researcharticleId_arr = array();
            $researcharticle_data = array();
            foreach($researcharticle_arr as $key => $value){
                $researcharticleId_arr[$key] = $value['Researcharticle']['id'];
                $researcharticle_data[$key] = $researcharticle_arr[$key]['Researcharticle'];
            }
            
            $researchproject_arr = $this->Researchproject->find('all', array(
                    'conditions' => array('Researchproject.applicant_id' => $this->Session->read('applicant_id'))));
            $researchprojectId_arr = array();
            $researchproject_data = array();
            foreach($researchproject_arr as $key => $value){
                $researchprojectId_arr[$key] = $value['Researchproject']['id'];
                $researchproject_data[$key] = $researchproject_arr[$key]['Researchproject'];
            }
            
            $misc = $this->Applicant->find('all', array(
                        'conditions' => array('Applicant.id' => $this->Session->read('applicant_id'))));
            if(count($misc) > 1) {
                $this->Session->setFlash('An error has occured. Please contact Support.');
                return false;
            }
            /*$api = $this->ApiScore->find('all', array(
                        'conditions' => array('ApiScore.applicant_id' => $this->Session->read('applicant_id'))));
            if(count($api) > 1) {
               $this->Session->setFlash('An error has occured. Please contact Support.');
                return false;
            }
            else if(count($api) == 0) {
                $this->ApiScore->create();
                $this->ApiScore->set(array(
                    'applicant_id' => $this->Session->read('applicant_id')));
                $this->ApiScore->save();
                $this->ApiScore->id = $this->ApiScore->getLastInsertId();
                $api = $this->ApiScore->find('all', array(
                        'conditions' => array('ApiScore.applicant_id' => $this->Session->read('applicant_id'))));
            }*/
            $this->request->data = array('Researchpaper' => $researchpaper_data,
                                         'Researcharticle' => $researcharticle_data,
                                         'Researchproject' => $researchproject_data,
                                         'Applicant' => $misc['0']['Applicant']);
                                         //'ApiScore' => $api['0']['ApiScore']);
        }
        
        private function getJsonObject($misc = array()) {
            $obj = array( '0' => array( 'mem_pro_bodies' => $misc['0']['Applicant']['mem_pro_bodies']),
                          '1' => array( 'convicted' => $misc['0']['Applicant']['convicted']),
                          '2' => array( 'pending_court' => $misc['0']['Applicant']['pending_court']),
                          '3' => array( 'willg_min_pay' => $misc['0']['Applicant']['willg_min_pay'])
                        );
            return $obj;
        }
        
        function _processFourth($count = 1) {
            $applicant = $this->Applicant->find('all', array(
                        'conditions' => array('Applicant.id' => $this->Session->read('applicant_id'))));
            if($this->data['modified_papers'] == 'true') {
                $researchpaper_arr = $this->Researchpaper->deleteAll( array('Researchpaper.applicant_id' => $this->Session->read('applicant_id')));
            }
            //print_r($this->data);
            if($this->data['modified_articles'] == 'true') {
                $researchpaper_arr = $this->Researcharticle->deleteAll( array('Researcharticle.applicant_id' => $this->Session->read('applicant_id')));
            }
            
            if($this->data['modified_rp'] == 'true') {
                $researchpaper_arr = $this->Researchproject->deleteAll( array('Researchproject.applicant_id' => $this->Session->read('applicant_id')));
            }
            
            if($this->Researchpaper->saveMany($this->data['Researchpaper']) && $this->Researcharticle->saveMany($this->data['Researcharticle'])
                    && $this->Applicant->save($this->data['Applicant']) && $this->Researchproject->saveMany($this->data['Researchproject'])
                    ) {
                if(false) { //$applicant['0']['Applicant']['post_applied_for'] == "Professor" || $applicant['0']['Applicant']['post_applied_for'] == "Associate Professor") {
                    if($this->ApiScore->save($this->data['ApiScore']))
                        return true;
                    else
                        return false;
                }
                return true;
            }
            return false;
	}
        
        function _prepareFifth() {
            $applicant = $this->Applicant->find('all', array(
                    'conditions' => array('Applicant.id' => $this->Session->read('applicant_id'))));

            $data['Applicant'] = $applicant['0']['Applicant'];
            $data['Applicantext'] = $applicant['0']['Applicant'];
            if(count($applicant) == 1) {
                $this->request->data = $data;
                $this->set('json_radio', $this->getJsonObject($applicant));
                //$this->Session->write('MultiStepForm.miscIdEighth', $misc['0']['Misc']['id']);
            }
            else if(count($applicant) > 1) {
                $this->Session->setFlash('An error has occured. Please contact Support.');
                return false;
            }
        }
        
        function _processFifth($count = 1) {
            //debug($this->data); return false;
            if($this->Applicant->save($this->data['Applicant']) && $this->Applicantext->save($this->data['Applicantext'])) {
                return true;
            }
            $this->Session->setFlash('An error has occured during saving data (P5).');
            return false;
	}
        
        function _prepareSixth() {
            $images = $this->Document->find('all', array(
                    'conditions' => array('Document.applicant_id' => $this->Session->read('applicant_id'))));
            $applicant = $this->Applicant->find('all', array(
                    'conditions' => array('Applicant.id' => $this->Session->read('applicant_id'))));
            
            if(count($applicant) == 1) {
                $this->set('applicant', $applicant['0']);
            }
            
            if(count($images) == 1) {
                $this->request->data = $images['0'];
                
                //$this->Session->write('MultiStepForm.imageId', $images['0']['Image']['id']);
            }
            else if(count($images) > 1) {
                $this->Session->setFlash('An error has occured. Please contact Support.');
            }
        }
        
        function _processSixth() {
            //print_r($this->data); return false;
            if(!empty($this->data['Document']['filename']['error']) && $this->data['Document']['filename']['error'] == 4
                && !empty($this->data['Document']['filename2']['error']) && $this->data['Document']['filename2']['error'] == 4
                && !empty($this->data['Document']['filename3']['error']) && $this->data['Document']['filename3']['error'] == 4
                && !empty($this->data['Document']['filename4']['error']) && $this->data['Document']['filename4']['error'] == 4
                && !empty($this->data['Document']['filename5']['error']) && $this->data['Document']['filename5']['error'] == 4
               )
            return true;
            
            if ($this->Document->save($this->data['Document'])) {
                //$this->Session->setFlash('Your documents have been submitted');
                //$this->redirect(array('controller'=>'form', 'action' => 'print_bfs'));
                return true;
            }
            return false;
        }
        
	function _prepareSeventh($count = 1) {
            $images = $this->Document->find('all', array(
                    'conditions' => array('Document.applicant_id' => $this->Session->read('applicant_id'))));
            
            if(count($images) == 1) {
                //$this->request->data = $images['0'];
                if((isset($images['0']['Document']['filename']) && empty($images['0']['Document']['filename']))
                    || (isset($images['0']['Document']['filename2']) && empty($images['0']['Document']['filename2']))
                    || (isset($images['0']['Document']['filename4']) && empty($images['0']['Document']['filename4']))
                )
                {
                    if(isset($images['0']['Document']['filename']) && empty($images['0']['Document']['filename']))
                        $this->Session->setFlash('Photograph is compulsory');
                    if(isset($images['0']['Document']['filename2']) && empty($images['0']['Document']['filename2']))
                        $this->Session->setFlash('Date of Birth Certificate is compulsory');
                    if(isset($images['0']['Document']['filename4']) && empty($images['0']['Document']['filename4']))
                        $this->Session->setFlash('Signature is compulsory');
                    $this->wizard('sixth');
                }
                $this->set('image', $images['0']);
                //print_r($this->request->data);
            }
            else if(count($images) > 1) {
                $this->Session->setFlash('An error has occured. Please contact Support.');
            }
            else {
                $this->Session->setFlash('Kindly upload the necessary documents.');
                $this->wizard('sixth');
            }
	}

	function _processSeventh($count = 1) {
            return true;
	}
        
	function _prepareEighth($count = 1) {
            $applicants = $this->NewAPIScore->find('all', array(
                            'conditions' => array('NewAPIScore.id' => $this->Session->read('applicant_id'))));
            if($applicants[0]['NewAPIScore']['post_applied_for'] === "Assistant Professor") {
                $this->wizard('ninth');
            }
            else {
                $this->request->data = $applicants[0];
            }
	}
        
        function _processEighth() {
            //debug($this->data['Applicant']); return false;
            $this->NewAPIScore->create();    
            $this->NewAPIScore->set($this->data);
            if($this->NewAPIScore->validates()) { //&& $this->User->validates()) {
                $this->NewAPIScore->save();
                return true;
            }
            return false;
        }
        
        function _prepareNinth($count = 1) {
            $applicants = $this->Applicant->find('all', array(
                            'conditions' => array('Applicant.id' => $this->Session->read('applicant_id'))));
            $this->set('payment_status', $applicants['0']['Applicant']['response_code']);
	}
        
        function _processNinth() {
            
        }
        
        function index($count = 1) {
        if ($this->request->is('post')) {
            if ($this->Auth->login()) {
                $this->set('data_set', 'false');
                $applicant_number=intval($this->data['Applicant']['applicant_number']);
                if(!is_numeric($applicant_number)) {
                    $this->Session->setFlash('Please enter numbers only.');
                    return false;
                }
                $applicants = $this->Applicant->find('all', array(
                    'conditions' => array('Applicant.user_id' => $applicant_number)));
                if (count($applicants) == 0) {
                    return false;
                }
                $education_arr = $this->Education->find('all', array(
                    'conditions' => array('Education.user_id' => $applicant_number)));

                $exp_arr = $this->Experience->find('all', array(
                    'conditions' => array('Experience.user_id' => $applicant_number)));
                $miscexp = $this->Miscexp->find('all', array(
                    'conditions' => array('Miscexp.user_id' => $applicant_number)));
                $adacdemic_dist = $this->Academic_dist->find('all', array(
                    'conditions' => array('Academic_dist.user_id' => $applicant_number)));
                $image = $this->Image->find('all', array(
                    'conditions' => array('Image.user_id' => $applicant_number)));

                $researchpapers = $this->Researchpaper->find('all', array(
                    'conditions' => array('Researchpaper.user_id' => $applicant_number)));
                $researcharticles = $this->Researcharticle->find('all', array(
                    'conditions' => array('Researcharticle.user_id' => $applicant_number)));
                $misc = $this->Misc->find('all', array(
                    'conditions' => array('Misc.user_id' => $applicant_number)));
                echo count($applicants) . count($education_arr) . count($exp_arr) . count($miscexp) . count($adacdemic_dist) . count($image) . count($researchpapers) . count($researcharticles) . count($misc) ;
                if (count($education_arr) == 0 || count($exp_arr) == 0 || count($miscexp) == 0 
                        || count($adacdemic_dist) == 0 || count($researchpapers) == 0 
                        || count($researcharticles) == 0 || count($misc) == 0 || count($image) == 0) {
                    $this->set('applicant', $applicants['0']);
                    if(count($education_arr) != 0) 
                        $this->set('education_arr', $education_arr);
                    if(count($exp_arr) != 0) 
                        $this->set('exp_arr', $exp_arr);
                    if(count($miscexp) != 0) 
                        $this->set('miscexp', $miscexp['0']);
                    if(count($adacdemic_dist) != 0)
                        $this->set('academic_dist', $adacdemic_dist);
                    if(count($researchpapers) != 0)
                        $this->set('researchpapers', $researchpapers);
                    if(count($researcharticles) != 0)
                        $this->set('researcharticles', $researcharticles);
                    if(count($image) != 0)
                        $this->set('image', $image['0']);
                    if(count($misc) != 0)
                        $this->set('misc', $misc['0']);
                    $this->set('data_set', 'true');
                }
                elseif (count($applicants) == 1 && (count($education_arr) == 7 || count($education_arr) == 12) 
                        && count($exp_arr) == 6 && count($miscexp) == 1 && count($adacdemic_dist) == 4 
                        && count($image) == 1 && count($researchpapers) == 10 && count($researcharticles) == 10 
                        && count($misc) == 1) {
                    $this->set('applicant', $applicants['0']);
                    $this->set('education_arr', $education_arr);
                    $this->set('exp_arr', $exp_arr);
                    $this->set('miscexp', $miscexp['0']);
                    $this->set('academic_dist', $adacdemic_dist);
                    $this->set('image', $image['0']);
                    $this->set('researchpapers', $researchpapers);
                    $this->set('researcharticles', $researcharticles);
                    $this->set('misc', $misc['0']);
                    $this->set('data_set', 'true');
                } else {
                    $this->Session->setFlash('An error has occured. Please contact Support.');
                    return false;
                }
            } else {
                $this->Session->setFlash(__('Invalid username or password'));
            }
        }
    }
    
    function getPostAppliedFor() {
        $current_post_applied = !empty($this->request->query['post']) ? $this->request->query['post'] : NULL;
        if (!empty($current_post_applied)) {
            //$this->set('postAppliedFor', $current_post_applied);
            $this->Session->write(Configure::read('GENERALINFO.post'), $current_post_applied);
            return $current_post_applied;
        } else if (!empty($this->Session->read(Configure::read('GENERALINFO.post')))) {
            //$this->set('postAppliedFor', $this->Session->read('post_applied_for'));
            return $this->Session->read(Configure::read('GENERALINFO.post'));
        } else {
            $this->Session->setFlash('Please select a post and then continue.');
            $this->redirect(array('controller' => 'form', 'action' => 'generalinformation'));
        }
    }
    
    function getAreaAppliedFor() {
        $current_area_applied = !empty($this->request->query['area']) ? $this->request->query['area'] : NULL;
        if (!empty($current_area_applied)) {
            //$this->set('postAppliedFor', $current_post_applied);
            $this->Session->write(Configure::read('GENERALINFO.area'), $current_area_applied);
            return $current_area_applied;
        } else if (!empty($this->Session->read(Configure::read('GENERALINFO.area')))) {
            //$this->set('postAppliedFor', $this->Session->read('post_applied_for'));
            return $this->Session->read(Configure::read('GENERALINFO.area'));
        } else {
            $this->Session->setFlash('Please select Area and then continue.');
            $this->redirect(array('controller' => 'form', 'action' => 'generalinformation'));
        }
    }
    
    function getCentreAppliedFor() {
        $current_centre_applied = !empty($this->request->query['centre']) ? $this->request->query['centre'] : NULL;
        if (!empty($current_centre_applied)) {
            //$this->set('postAppliedFor', $current_post_applied);
            $this->Session->write(Configure::read('GENERALINFO.centre'), $current_centre_applied);
            return $current_centre_applied;
        } else if (!empty($this->Session->read(Configure::read('GENERALINFO.centre')))) {
            //$this->set('postAppliedFor', $this->Session->read('post_applied_for'));
            return $this->Session->read(Configure::read('GENERALINFO.centre'));
        } else {
            $this->Session->setFlash('Please select a Centre and then continue.');
            $this->redirect(array('controller' => 'form', 'action' => 'generalinformation'));
        }
    }
    
    private function checkAgeAsPerPost($age, $post, $category, $pwd, $dep, $internal) {
        $relaxation = 0;
        if($post == "Deputy Librarian") { //45 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 50;
	                return $relaxation;
		}
		else {
			$relaxation = 45;
	                return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 50;
                return $relaxation;
            }
        }
        if($post == "Deputy Registrar") { //45 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 50;
	                return $relaxation;
		}
		else {
                	$relaxation = 45;
	                return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 50;
                return $relaxation;
            }
        }
        if($post == "Assistant Librarian") { //35 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 40;
	                return $relaxation;
		}
		else {
                	$relaxation = 35;
                	return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
        }
        if($post == "Assistant Registrar") { //35 y
            if($category == "OBC" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 43;
	                return $relaxation;
		}
		else {
                	$relaxation = 38;
                	return $relaxation;
		}
            }
            else if($category == "OBC" && $dep == "yes") {
                $relaxation = 43;
                return $relaxation;
            }
        }
        if($post == "Information Scientist") { //40 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 45;
	                return $relaxation;
		}
		else {
                	$relaxation = 40;
                	return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 45;
                return $relaxation;
            }
        }
        if($post == "Public Relations Officer") { //40 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 45;
	                return $relaxation;
		}
		else {
                	$relaxation = 40;
                	return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 45;
                return $relaxation;
            }
        }
        if($post == "Technical Officer (Laboratory)") { //40 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 45;
	                return $relaxation;
		}
		else {
                	$relaxation = 40;
                	return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 45;
                return $relaxation;
            }
        }
        if($post == "Security Officer") { //50 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 55;
	                return $relaxation;
		}
		else {
                	$relaxation = 50;
                	return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 55;
                return $relaxation;
            }
        }
        if($post == "Nurse") { //35 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 40;
	                return $relaxation;
		}
		else {
                	$relaxation = 35;
                	return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
        }
        if($post == "Private Secretary") { //40 y
            if($category == "OBC" && $pwd == "yes") {
                if($dep == "no") {
			if($internal == "yes") {
				$relaxation = 58;
	                	return $relaxation;
			}
			else {
                    		$relaxation = 53;
		                return $relaxation;
			}
                }
                else {
                    $relaxation = 58;
                    return $relaxation;
                }
            }
            else if($category == "OBC" && $pwd == "no") {
                if($dep == "no") {
			if($internal == "yes") {
				$relaxation = 48;
	                	return $relaxation;
			}
			else {
                    		$relaxation = 43;
		                return $relaxation;
			}                
                }
                else {
                    $relaxation = 48;
                    return $relaxation;
                }
            }
            else if($category == "General" && $pwd == "yes"){
                if($dep == "no") {
			if($internal == "yes") {
				$relaxation = 55;
	                	return $relaxation;
			}
			else {
                    		$relaxation = 50;
		                return $relaxation;
			}
                }
                else {
                    $relaxation = 55;
                    return $relaxation;
                }
                
            }
            else if($category == "General" && $pwd == "no"){
                if($dep == "no") {
			if($internal == "yes") {
				$relaxation = 45;
	                	return $relaxation;
			}
			else {
                    		$relaxation = 40;
		                return $relaxation;
			}
                }
                else {
                    $relaxation = 45;
                    return $relaxation;
                }
            }
        }
        if($post == "Personal Assistant") { //35 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 40;
	                return $relaxation;
		}
		else {
                	$relaxation = 35;
		        return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
        }
        if($post == "Assistant") { //35 y
            if($category == "OBC" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 43;
	                return $relaxation;
		}
		else {
                	$relaxation = 38;
		        return $relaxation;
		}
            }
            else if($category == "OBC" && $dep == "yes") {
                $relaxation = 43;
                return $relaxation;
            }
        }
        if($post == "Junior Engineer (Elect)") { //35 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 40;
	                return $relaxation;
		}
		else {
                	$relaxation = 35;
		        return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
        }
        if($post == "Estate Officer") { //35 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 40;
	                return $relaxation;
		}
		else {
                	$relaxation = 35;
		        return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
        }
        if($post == "Senior Technical Assistant (Computer)") { //35 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 40;
	                return $relaxation;
		}
		else {
                	$relaxation = 35;
		        return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
        }
        if($post == "Senior Technical Assistant (Laboratory)") { //35 y
            if($category == "OBC" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 43;
	                return $relaxation;
		}
		else {
                	$relaxation = 38;
		        return $relaxation;
		}
            }
            else if($category == "OBC" && $dep == "yes") {
                $relaxation = 43;
                return $relaxation;
            }
            else if($category == "General" && $dep == "no"){
		if($internal == "yes") {
			$relaxation = 40;
	                return $relaxation;
		}
		else {
                	$relaxation = 35;
		        return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes"){
                $relaxation = 40;
                return $relaxation;
            }
        }
        if($post == "Semi Professional Assistant") { //35 y
            if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
            else if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 40;
	                return $relaxation;
		}
		else {
                	$relaxation = 35;
		        return $relaxation;
		}
            }
        }
        if($post == "Pharmacist") { //30 y
            if($pwd == "yes" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
            else if($pwd == "yes" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 45;
	                return $relaxation;
		}
		else {
                	$relaxation = 40;
		        return $relaxation;
		}
            }
        }
        if($post == "Technical Assistant") { //35 y
            if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
            else if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 40;
	                return $relaxation;
		}
		else {
                	$relaxation = 35;
		        return $relaxation;
		}
            }
        }
        if($post == "Security Inspector") { //40 y
            if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
            else if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 45;
	                return $relaxation;
		}
		else {
                	$relaxation = 40;
		        return $relaxation;
		}
            }
        }
        if($post == "Laboratory Assistant") { //30 y
            if($category == "SC" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
            else if($category == "SC" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 40;
	                return $relaxation;
		}
		else {
                	$relaxation = 35;
		        return $relaxation;
		}
            }
            else if($category == "OBC" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
            else if($category == "OBC" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 38;
	                return $relaxation;
		}
		else {
                	$relaxation = 33;
		        return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
            else if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 35;
	                return $relaxation;
		}
		else {
                	$relaxation = 30;
		        return $relaxation;
		}
            }
        }
        if($post == "Library Assistant") { //30 y
            if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
            else if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 35;
	                return $relaxation;
		}
		else {
                	$relaxation = 30;
		        return $relaxation;
		}
            }
        }
        if($post == "Lower Division Clerk") { //30 y
            if(($category == "SC" || $category == "ST") && $pwd == "no") {
                if($dep == "yes") {
                    $relaxation = 40;
                    return $relaxation;
                }
                else {
			if($internal == "yes") {
				$relaxation = 40;
	                	return $relaxation;
			}
			else {
                		$relaxation = 35;
		        	return $relaxation;
			}
                }
            }
            else if(($category == "SC" || $category == "ST") && $pwd == "yes") {
                if($dep == "yes") {
                    $relaxation = 45;
                    return $relaxation;
                }
                else {
			if($internal == "yes") {
				$relaxation = 50;
	                	return $relaxation;
			}
			else {
                		$relaxation = 45;
		        	return $relaxation;
			}
                }
            }
            else if($category == "OBC" & $pwd == "no") {
                if($dep == "yes") {
                    $relaxation = 40;
                    return $relaxation;
                }
                else {
			if($internal == "yes") {
				$relaxation = 38;
	                	return $relaxation;
			}
			else {
                		$relaxation = 33;
		        	return $relaxation;
			}
                }
                
            }
            else if($category == "OBC" & $pwd == "yes") {
                if($dep == "yes") {
                    $relaxation = 43;
                    return $relaxation;
                }
                else {
			if($internal == "yes") {
				$relaxation = 48;
	                	return $relaxation;
			}
			else {
                		$relaxation = 43;
		        	return $relaxation;
			}
                }
                
            }
            else if($category == "General" & $pwd == "no") {
                if($dep == "yes") {
                    $relaxation = 40;
                    return $relaxation;
                }
                else {
			if($internal == "yes") {
				$relaxation = 35;
	                	return $relaxation;
			}
			else {
                		$relaxation = 30;
		        	return $relaxation;
			}
                }
            }
            else if($category == "General" & $pwd == "yes") {
                if($dep == "yes") {
                    $relaxation = 40;
                    return $relaxation;
                }
                else {
			if($internal == "yes") {
				$relaxation = 45;
	                	return $relaxation;
			}
			else {
                		$relaxation = 40;
		        	return $relaxation;
			}
                }
            }
        }
        if($post == "Hindi Typist") { //25 y
            if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
            else if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 30;
                	return $relaxation;
		}
		else {
               		$relaxation = 25;
	        	return $relaxation;
		}
            }
        }
        if($post == "Cook") { //30 y
            if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
            else if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 35;
                	return $relaxation;
		}
		else {
               		$relaxation = 30;
	        	return $relaxation;
		}
            }
        }
        if($post == "Library Attendant") { //30 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 35;
                	return $relaxation;
		}
		else {
               		$relaxation = 30;
	        	return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
            else if($category == "OBC" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 38;
                	return $relaxation;
		}
		else {
               		$relaxation = 33;
	        	return $relaxation;
		}
            }
            else if($category == "OBC" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
        }
        if($post == "Laboratory Attendant") { //30 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 35;
                	return $relaxation;
		}
		else {
               		$relaxation = 30;
	        	return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
            else if($category == "OBC" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 38;
                	return $relaxation;
		}
		else {
               		$relaxation = 33;
	        	return $relaxation;
		}
            }
            else if($category == "OBC" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
            else if($category == "SC" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 40;
                	return $relaxation;
		}
		else {
               		$relaxation = 35;
	        	return $relaxation;
		}
            }
            else if($category == "SC" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
        }
        if($post == "Office Attendant") { //30 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 35;
                	return $relaxation;
		}
		else {
               		$relaxation = 30;
	        	return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
        }
        if($post == "Hostel Attendant") { //30 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 35;
                	return $relaxation;
		}
		else {
               		$relaxation = 30;
	        	return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
        }
        if($post == "Multi Tasking Staff") { //30 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 35;
                	return $relaxation;
		}
		else {
               		$relaxation = 30;
	        	return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
        }
        if($post == "Kitchen Attendant") { //30 y
            if($category == "General" && $dep == "no") {
		if($internal == "yes") {
			$relaxation = 35;
                	return $relaxation;
		}
		else {
               		$relaxation = 30;
	        	return $relaxation;
		}
            }
            else if($category == "General" && $dep == "yes") {
                $relaxation = 40;
                return $relaxation;
            }
        }
        return $relaxation;
    }

}
?>
